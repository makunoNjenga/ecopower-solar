<script setup>
import { computed } from "vue";
import { useCurrency } from "@/composables/useCurrency";

const props = defineProps({
    amount: {
        type: [Number, String],
        required: true,
    },
    showSymbol: {
        type: Boolean,
        default: true,
    },
    salePrice: {
        type: [Number, String],
        default: null,
    },
    showDiscount: {
        type: Boolean,
        default: false,
    },
});

const { formatPrice, calculateDiscount } = useCurrency();

const formattedPrice = computed(() =>
    formatPrice(props.amount, props.showSymbol)
);
const formattedSalePrice = computed(() =>
    props.salePrice ? formatPrice(props.salePrice, props.showSymbol) : null
);
const discountPercentage = computed(() =>
    props.salePrice ? calculateDiscount(props.amount, props.salePrice) : 0
);
</script>

<template>
    <span class="price-display">
        <span v-if="salePrice" class="line-through text-gray-500 text-sm mr-2">
            {{ formattedPrice }}
        </span>
        <span :class="salePrice ? 'text-green-600 font-semibold' : ''">
            {{ salePrice ? formattedSalePrice : formattedPrice }}
        </span>
        <span
            v-if="showDiscount && discountPercentage > 0"
            class="ml-2 text-xs bg-red-500 text-white px-2 py-0.5 rounded"
        >
            -{{ discountPercentage }}%
        </span>
    </span>
</template>

<style scoped>
.price-display {
    @apply inline-flex items-center;
}
</style>
