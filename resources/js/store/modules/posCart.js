import _ from "lodash";
import activityEnum from "../../enums/modules/activityEnum";
import orderTypeEnum from "../../enums/modules/orderTypeEnum";
import { parse } from "vue/compiler-sfc";


export const posCart = {
    namespaced: true,
    state: {
        lists: [],
        subtotal: 0,
        totalTax: 0,
        discount: 0,
        discountPercentage: 0,
        orderType: null,
        order: null,  //For Add Order
        selectedMember: null, // Add selectedMember to store
    },
    getters: {
        lists: function (state) { 
            return state.lists;
        },
        subtotal: function (state) {
            return state.subtotal;
        },
        totalTax: function (state) {
            return state.totalTax;
        },
        discount: function (state) {
            return state.discount;
        },
        discountPercentage: function (state) {
            return state.discountPercentage;
        },
        orderType: function (state) {
            return state.orderType;
        },
        order: function (state) {
            return state.order;
        },
        selectedMember: function (state) {
            return state.selectedMember;
        }
    },
    actions: {
        lists: function (context, payload) { 
            context.commit("lists", payload);
            context.commit("subtotal");
            context.commit("discount",0);
        },
        quantity: function (context, payload) {
            context.commit("quantity", payload);
            context.commit("discount",0);
            context.commit("subtotal");
        },
        deleteCartItem: function (context, payload) {
            context.commit("deleteCartItem", payload);
            context.commit("subtotal");
            context.commit("discount",0);
        },
        discount: function (context, payload) {
            context.commit("discount", payload);
            context.commit("subtotal");
        },
        destroyDiscount: function (context) {
            context.commit('discount', 0);
        },
        resetCart: function (context) {
            context.commit('resetCart');
        },
        setOrder: function (context, payload) {
            context.commit('setOrder', payload);
        },
        setSelectedMember: function (context, payload) {
            context.commit('setSelectedMember', payload);
        },
        updateCustomName: function (context, payload) {
            context.commit('updateCustomName', payload);
        },
        updateCustomItem: function (context, payload) {
            context.commit('updateCustomItem', payload);
            context.commit('subtotal');
        }
    },
    mutations: {
        lists: function (state, payload) {   
            if (payload.length > 0) {
                let isNew = false;
                let newChecker = [];
                let variationAndExtraChecker = [];
                _.forEach(payload, (pay) => {
                    if (state.lists.length === 0) {
                        isNew = true;
                    } else {
                        isNew = true;
                        _.forEach(state.lists, (list, listKey) => {
                            if (list.item_id === pay.item_id) {

                                if (state.lists[listKey].item_variations.variations !== "undefined") {
                                    if (Object.keys(state.lists[listKey].item_variations.variations).length !== 0) {
                                        _.forEach(state.lists[listKey].item_variations.variations, (variationId, variationKey) => {
                                            if (pay.item_variations.variations[variationKey] !== "undefined" && pay.item_variations.variations[variationKey] === variationId) {
                                                variationAndExtraChecker.push(true);
                                            } else {
                                                variationAndExtraChecker.push(false);
                                            }
                                        });
                                    }
                                }

                                if (pay.item_extras.extras.length !== 0 && state.lists[listKey].item_extras.extras.length !== 0) {
                                    _.forEach(pay.item_extras.extras, (payExtra) => {
                                        if (state.lists[listKey].item_extras.extras.includes(payExtra) && state.lists[listKey].item_extras.extras.length === pay.item_extras.extras.length) {
                                            variationAndExtraChecker.push(true);
                                        } else {
                                            variationAndExtraChecker.push(false);
                                        }
                                    });
                                } else {
                                    if (pay.item_extras.extras.length === state.lists[listKey].item_extras.extras.length) {
                                        variationAndExtraChecker.push(true);
                                    } else {
                                        variationAndExtraChecker.push(false);
                                    }
                                }

                                if (variationAndExtraChecker.includes(false)) {
                                    newChecker.push(false);
                                } else {
                                    // Check if order_item_custom_name matches
                                    const existingCustomName = state.lists[listKey].order_item_custom_name || null;
                                    const newCustomName = pay.order_item_custom_name || null;
                                    
                                    if (existingCustomName === newCustomName) {
                                        // Same custom name or both null, combine items
                                        newChecker.push(true);
                                        state.lists[listKey].quantity += pay.quantity;
                                    } else {
                                        // Different custom names, treat as separate items
                                        newChecker.push(false);
                                    }
                                }
                                variationAndExtraChecker = [];
                            } else {
                                newChecker.push(false);
                            }
                        });

                        _.forEach(newChecker, (check) => {
                            if (check) {
                                isNew = false;
                            }
                        });
                        newChecker = [];
                    }

                    if (isNew) {
                        state.lists.push({
                            discount: pay.discount,
                            image: pay.image,
                            instruction: pay.instruction,
                            item_extra_total: pay.item_extra_total,
                            item_extras: pay.item_extras,
                            item_id: pay.item_id,
                            item_variation_total: pay.item_variation_total,
                            item_variations: pay.item_variations,
                            name: pay.name,
                            currency_price: pay.currency_price,
                            convert_price: pay.convert_price,
                            quantity: pay.quantity,

                            price_with_tax: pay.price_with_tax,
                            tax_amount: pay.tax_amount,
                            can_input_custom_name: pay.can_input_custom_name || null,
                            order_item_custom_name: pay.order_item_custom_name || null,
                        });
                        isNew = false;
                    }
                });
            } 
        },
        subtotal: function (state) {
            if (state.lists.length > 0) {
                let subtotal = 0;
                let totalTax = 0;
                _.forEach(state.lists, (list, listKey) => {
                    let itemSubtotal = ((list.convert_price + list.item_variation_total + list.item_extra_total ) * list.quantity);
                    let itemDiscount = list.discount || 0;
                    state.lists[listKey].total = itemSubtotal - itemDiscount; // Total Price after item discount

                    state.lists[listKey].total_tax = (list.tax_amount * 100) * (list.quantity * 100) / 10000; // Total Tax amount
                    state.lists[listKey].total_price_with_tax = state.lists[listKey].total + state.lists[listKey].total_tax; // Total Price with tax

                    subtotal += state.lists[listKey].total;

                    totalTax += parseFloat(state.lists[listKey].total_tax || 0);

                    if(state.discountPercentage > 0) {
                        totalTax = (totalTax * (100 - state.discountPercentage)) / 100;
                    }
                });

                state.subtotal = subtotal;
                state.totalTax = totalTax;
            } else {
                state.subtotal = 0;
                state.totalTax = 0;
            }
        },
        // totalTax: function (state) {
        //     if (state.lists.length > 0) {
        //         let totalTax = 0;
        //         _.forEach(state.lists, (list) => {
        //             totalTax += list.tax_amount;
        //         });
        //         state.totalTax = totalTax;
        //     } else {
        //         state.totalTax = 0;
        //     }
        // },
        quantity: function (state, payload) {
            if (payload.status === "increment") {
                state.lists[payload.id].quantity++;
            } else if (payload.status === "decrement") {
                if (state.lists[payload.id].quantity === 1) {
                    state.lists.splice(payload.id, 1);
                    state.discount = 0;
                } else {
                    state.lists[payload.id].quantity--;
                }
            } else {
                state.lists[payload.id].quantity = payload.status;
            }
        },
        deleteCartItem: function (state, payload) {
            if (payload.status === "decrement") {
                state.lists.splice(payload.id,1);
            }
        },
        discount: function (state, payload) { 
            state.discount = payload;
            state.discountPercentage = parseFloat((payload * 100 / state.subtotal).toFixed(2));
        },
        resetCart: function (state) {
            state.lists = [];
            state.subtotal = 0;
            state.discount = 0;
            state.totalTax = 0;
            state.discountPercentage = 0;
            state.order = null;
            state.selectedMember = null;
        },
        setOrder: function (state, payload) {
            state.order = payload;
        },
        setSelectedMember: function (state, payload) {
            state.selectedMember = payload;
        },
        updateCustomName: function (state, payload) {
            if (state.lists[payload.index]) {
                state.lists[payload.index].order_item_custom_name = payload.customName;
            }
        },
        updateCustomItem: function (state, payload) {
            if (state.lists[payload.index]) {
                // Update custom name
                state.lists[payload.index].order_item_custom_name = payload.customName;
                
                // Update quantity
                state.lists[payload.index].quantity = payload.quantity;
                
                // Update unit price (convert_price is the base price)
                state.lists[payload.index].convert_price = payload.unitPrice;
                
                // Update discount values
                state.lists[payload.index].discount = payload.discount;
                state.lists[payload.index].discount_percentage = payload.discountPercentage;
                
                // Recalculate total for this item
                let itemSubtotal = (payload.unitPrice + state.lists[payload.index].item_variation_total + state.lists[payload.index].item_extra_total) * payload.quantity;
                state.lists[payload.index].total = itemSubtotal - payload.discount;
                
                // Recalculate tax if applicable
                if (state.lists[payload.index].tax_amount) {
                    state.lists[payload.index].total_tax = (state.lists[payload.index].tax_amount * 100) * (payload.quantity * 100) / 10000;
                    state.lists[payload.index].total_price_with_tax = state.lists[payload.index].total + state.lists[payload.index].total_tax;
                }
            }
        },
    },
};
