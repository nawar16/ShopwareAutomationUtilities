<?php declare(strict_types=1);

namespace LocalProductOptPlugin\Administration\Controller;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route(defaults: ['_routeScope' => ['api']])]
class ApiController extends AbstractController
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly HttpClientInterface $httpClient,
        private readonly EntityRepository $productRepository
    ) {}

    #[Route(path: '/api/_action/local-product-opt-plugin/trigger/{productId}', name: 'api.action.local_product_opt_plugin.trigger', methods: ['POST'])]
    public function triggerOptimization(string $productId, Context $context): JsonResponse
    {
        $criteria = new Criteria([$productId]);
        $product = $this->productRepository->search($criteria, $context)->first();

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $baseUrl = $this->systemConfigService->getString('LocalProductOptPlugin.config.apiEndpoint');
        if (empty($baseUrl)) {
            $baseUrl = 'http://127.0.0.1:8000';
        }

        $pythonPayload = [
            'sku' => $product->getProductNumber(),
            'name' => $product->getName() ?? '',
            'description' => $product->getDescription() ?? '',
            'keywords' => [], 
            'taxId' => $product->getTaxId(),
            'price' => 19.99 
        ];

        try {
            $response = $this->httpClient->request('POST', rtrim($baseUrl, '/') . '/optimize/shopware-payload', [
                'json' => $pythonPayload,
                'headers' => ['Content-Type' => 'application/json']
            ]);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return new JsonResponse([
                    'error' => 'Python API returned an error',
                    'details' => $response->getContent(false)
                ], Response::HTTP_BAD_GATEWAY);
            }

            $responseData = $response->toArray();
            $shopwarePayload = $responseData['shopware_api_payload'] ?? null;

            if (!$shopwarePayload) {
                return new JsonResponse(['error' => 'Invalid serialization returned from Python endpoint'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $shopwarePayload['id'] = $productId;
            $this->productRepository->update([$shopwarePayload], $context);

            return new JsonResponse(['success' => true, 'updated_fields' => $shopwarePayload]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Failed communicating with local AI processing node',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
