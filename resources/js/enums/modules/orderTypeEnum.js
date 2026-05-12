const orderTypeEnum = Object.freeze({
    DELIVERY: 5,
    TAKEAWAY: 10,
    POS: 15,    // TODO: Should not have type POS, need to change to something else
    DINING_TABLE: 20,
    TOKEN: 25,
    ONLINE_ORDER: 30
});
export default orderTypeEnum;
