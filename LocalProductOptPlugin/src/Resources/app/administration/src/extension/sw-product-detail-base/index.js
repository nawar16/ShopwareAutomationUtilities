import template from './sw-product-detail-base.html.twig';

const { Component, Mixin } = Shopware;

Component.override('sw-product-detail-base', {
    template,
    mixins: [
        Mixin.getByName('notification')
    ],
    inject: [
        'httpClient'
    ],
    data() {
        return {
            isOptimizing: false
        };
    },
    methods: {
        onOptimizeProduct() {
            this.isOptimizing = true;
            const productId = this.product.id;
            const apiRoute = `/local-product-opt-plugin/trigger/${productId}`;
            this.httpClient.post(apiRoute, {}, {
                headers: {
                    Authorization: `Bearer ${Shopware.Context.api.authToken}`
                }
            }).then((response) => {
                if (response.data.success) {
                    this.createNotificationSuccess({
                        title: this.$tc('global.default.success'),
                        message: 'Product content optimized successfully!'
                    });
                    if (this.$parent && typeof this.$parent.loadData === 'function') {
                        this.$parent.loadData();
                    }
                } else {
                    this.showErrorNotification(response.data.error || 'Optimization failed.');
                }
            }).catch((error) => {
                const message = error.response?.data?.message || error.message;
                this.showErrorNotification(message);
            }).finally(() => {
                this.isOptimizing = false;
            });
        },

        showErrorNotification(message) {
            this.createNotificationError({
                title: this.$tc('global.default.error'),
                message: message
            });
        }
    }
});
