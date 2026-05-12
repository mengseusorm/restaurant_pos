<template>
    <div v-if="member" class="flex items-center gap-4">
        <div v-if="size == 'normal'">
            <h4 class="text-lg font-semibold">
                {{ $t('label.member_information') }}
            </h4>
            <div class="flex flex-col gap-1">
                <div class="flex flex-wrap items-center text-sm text-gray-500" v-if="member.name">
                    <i class="lab lab-user-line lab-font-size-16 me-1"></i>
                    <span class="min-w-[140px] font-medium">{{ $t('label.name') }}:</span>
                    <span class="break-all">{{ member.name }}</span>
                </div>
                <div class="flex flex-wrap items-center text-sm text-gray-500" v-if="member.phone">
                    <i class="lab lab-call-center lab-font-size-16 me-1"></i>
                    <span class="min-w-[140px] font-medium">{{ $t('label.phone') }}:</span>
                    <span class="break-all">{{ maskMiddle(member.phone) }}</span>
                </div>
                <div class="flex flex-wrap items-center text-sm text-gray-500" v-if="member.card_number">
                    <i class="lab lab-card lab-font-size-16 me-1"></i>
                    <span class="min-w-[140px] font-medium">{{ $t('label.card_number') }}:</span>
                    <span class="break-all">{{ maskMiddle(member.card_number) }}</span>
                </div>
                <div class="flex flex-wrap items-center text-sm text-gray-500">
                    <i class="lab lab-fill-moneys lab-font-size-16 me-1"></i>
                    <span class="min-w-[140px] font-medium">{{ $t('label.point_balance') }}:</span>
                    <span class="break-all">{{ member.point_balance }}</span>
                </div>
            </div>
        </div>
        <div v-else>
            <div class="flex items-center gap-1">
                <i class="lab lab-user-line lab-font-size-16"></i>
                <span v-if="member.name" class="font-semibold me-3">{{ member.name }}</span>

                <i class="lab lab-call-center lab-font-size-16"></i>
                <span v-if="member.phone" class="text-sm text-gray-500 me-3">
                    {{ maskMiddle(member.phone) }}
                </span>

                <i class="lab lab-card lab-font-size-16"></i>
                <span v-if="member.card_number" class="text-sm text-gray-500 me-3">
                    {{ maskMiddle(member.card_number) }}
                </span>

                <i class="lab lab-fill-moneys lab-font-size-16"></i>
                <span v-if="member.point_balance" class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-red-600 text-xs font-bold">
                    {{ member.point_balance }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'MemberInformationComponent',
    props: {
        member: { type: Object, required: true },
        size: { type: String, default: 'normal' },
    },
    methods: {
        maskMiddle: function (value) {
            if (!value) return '';
            const length = value.length;
            if (length <= 4) return value; // No masking needed for short values
            const middleStart = Math.floor((length - 2) / 2);
            const visibleStart = value.slice(0, middleStart);
            const maskedPart = '**';
            const visibleEnd = value.slice(middleStart + 2);
            return `${visibleStart}${maskedPart}${visibleEnd}`;
        },
    },
};
</script>
