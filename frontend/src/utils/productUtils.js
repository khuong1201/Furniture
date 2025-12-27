export const cartesianProduct = (arr) => {
    return arr.reduce((a, b) => a.flatMap(d => b.map(e => [d, e].flat())));
};

export const generateVariants = (selectedAttributes) => {
    if (selectedAttributes.length === 0) return [];
    
    const valuesArray = selectedAttributes.map(attr => attr.values);
    const combinations = cartesianProduct(valuesArray);

    return combinations.map(combo => {
        const comboArray = Array.isArray(combo) ? combo : [combo];
        return {
            sku: '', 
            price: 0,
            stock: [], 
            attributes: comboArray.map((val, idx) => ({
                attribute_slug: selectedAttributes[idx].slug,
                value: val
            }))
        };
    });
};