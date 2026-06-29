<?php declare(strict_types=1);

namespace TaxVatValidatorPlugin\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'vat:validate')]
class VatValidateCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $customerRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();
        $customers = $this->customerRepository->search(new Criteria(), $context)->getEntities();
        $payload = [];
        foreach ($customers as $customer) {
            $payload[] = [
                'customer_id' => $customer->getId(),
                'vat_id' => $customer->getVatIds()[0] ??
            ];
        }
        //handle py script in this path
        $process = new Process(['python3', '/usr/local/bin/vat_validator.py']);
        $process->setInput(json_encode($payload));
        $process->run(); 
        $results = json_decode($process->getOutput(), true);
        $updatePayload = [];
        foreach ($results as $result) {
            $updatePayload[] = [
                'id' => $result['customer_id'],
                'customFields' => [
                    'tax_vat_validator_status' => $result['status']
                ]
            ];
        }
        if (!empty($updatePayload)) {
            $this->customerRepository->update($updatePayload, $context);
            $output->writeln('Customers updated successfully!');
        }
        return Command::SUCCESS;
    }
}
