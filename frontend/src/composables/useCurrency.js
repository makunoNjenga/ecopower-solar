/**
 * Currency formatting composable
 * Provides utilities for formatting prices in Kenyan Shillings
 */
export function useCurrency() {
    /**
     * Format a number as Kenyan Shillings (KSh)
     * 
     * @param {number|string} amount - The amount to format
     * @param {boolean} includeSymbol - Whether to include the KSh symbol
     * @returns {string} Formatted currency string
     */
    const formatPrice = (amount, includeSymbol = true) => {
        // Convert to number if string
        const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
        
        // Return placeholder if invalid
        if (isNaN(numAmount) || numAmount === null || numAmount === undefined) {
            return includeSymbol ? 'KSh 0' : '0';
        }

        // Format with comma separators and no decimal points
        const formatted = Math.round(numAmount).toLocaleString('en-KE');
        
        return includeSymbol ? `KSh ${formatted}` : formatted;
    };

    /**
     * Format price for display without currency symbol
     * 
     * @param {number|string} amount - The amount to format
     * @returns {string} Formatted number string
     */
    const formatNumber = (amount) => {
        return formatPrice(amount, false);
    };

    /**
     * Calculate discount percentage
     * 
     * @param {number} originalPrice - Original price
     * @param {number} salePrice - Sale price
     * @returns {number} Discount percentage
     */
    const calculateDiscount = (originalPrice, salePrice) => {
        if (!originalPrice || !salePrice || salePrice >= originalPrice) {
            return 0;
        }
        return Math.round(((originalPrice - salePrice) / originalPrice) * 100);
    };

    return {
        formatPrice,
        formatNumber,
        calculateDiscount
    };
}

