<template>
  <div class="fixed bottom-0 w-full">
        <div class="w-full p-4 shadow-md z-50 border-t border-gray-300 bg-primary-light">
            <div
                class="flex items-center justify-center gap-6"
                v-if="carts.length > 0"
            >
                <button
                    @click.prevent="resetCart"
                    class="capitalize text-sm font-medium leading-6 font-rubik w-full text-center rounded-3xl py-2 text-white bg-[#FB4E4E]"
                >
                    {{ $t("button.cancel") }}
                </button>
                <button
                    @click.prevent="orderSubmit"
                    class="capitalize text-sm font-medium leading-6 font-rubik w-full text-center rounded-3xl py-2 text-white bg-[#1AB759]"
                >
                    {{ $t("button.order") }}
                </button>
                <button
                    @click.prevent="orderUnpaid"
                    class="capitalize text-sm font-medium leading-6 font-rubik w-full text-center rounded-3xl py-2 text-white bg-primary"
                    v-if="branch.show_unpaid_button == statusEnum.ACTIVE"
                >
                    {{ $t("button.unpaid") }}
                </button>
            </div>
        </div>
    </div>
</template>