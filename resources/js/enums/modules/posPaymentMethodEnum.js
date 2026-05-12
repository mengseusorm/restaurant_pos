
import { paymentMethod } from "../../store/modules/paymentMethod";

const posPaymentMethodEnum = Object.freeze({
    CASH          : 1,
    CARD          : 2, 
    ABA           : 5,
    ACLEDA        : 6,
    HUIONE        : 7,
    UNPAID        : 0,  
});
export default posPaymentMethodEnum;
