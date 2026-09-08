<script setup lang="ts">
import { Form, Head, Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import InputError from "@/components/InputError.vue";
import KilnMark from "@/components/KilnMark.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import { Input } from "@/components/ui/input";
import { Spinner } from "@/components/ui/spinner";
import { store } from "@/routes/login";
import { request } from "@/routes/password";

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const page = usePage();

const locale = ref<"en" | "bn">("en");
const email = ref("");
const password = ref("");
const selectedRole = ref<"admin" | "sales_shop" | null>(null);

function selectRole(role: "admin" | "sales_shop") {
    selectedRole.value = role;
    if (role === "admin") {
        email.value = "test@example.com";
        password.value = "password";
    } else {
        email.value = "sales@example.com";
        password.value = "password";
    }
}

const t = computed(() => {
    if (locale.value === "bn") {
        return {
            title: "স্টাফ সাইন ইন",
            subtitle: "আপনার শপ ইমেইল ও পাসওয়ার্ড দিয়ে প্রবেশ করুন।",
            emailLabel: "ইমেইল",
            emailPlaceholder: "email@example.com",
            passwordLabel: "পাসওয়ার্ড",
            forgotPassword: "পাসওয়ার্ড ভুলে গেছেন?",
            rememberMe: "মনে রাখুন",
            signInButton: "লগইন করুন",
            demoTitle: "দ্রুত ডেমো লগইন",
            demoClick: "ক্লিক করলেই পূরণ হবে",
            adminRole: "অ্যাডমিন / মালিক",
            adminDesc: "স্টক, ক্রয়, চালান ও লেজার",
            salesRole: "কাউন্টার সেলস",
            salesDesc: "পিওএস ও শোরুম বিক্রয়",
            leftHeadline: "স্টক, বিক্রয় ও গোডাউন — এক কাউন্টারে।",
            leftFeature1: "বক্স, পিস এবং স্কয়ারফুটে পিওএস বিক্রয়",
            leftFeature2: "ব্যাচ, শেড ও গ্রেডভিত্তিক সঠিক লট স্টক",
            leftFeature3: "লেজারভিত্তিক গ্রাহকের দেনা-পাওনা হিসাব",
            leftFooter: "শোরুম · গোডাউন · হিসাব বিভাগ",
        };
    }

    return {
        title: "Staff sign in",
        subtitle: "Use your shop email and password.",
        emailLabel: "Email",
        emailPlaceholder: "email@example.com",
        passwordLabel: "Password",
        forgotPassword: "Forgot password?",
        rememberMe: "Remember me",
        signInButton: "Sign in",
        demoTitle: "Quick Demo Switcher",
        demoClick: "Click to auto-fill",
        adminRole: "Admin / Owner",
        adminDesc: "Full shop, purchases & ledgers",
        salesRole: "Sales Counter",
        salesDesc: "POS, showroom & customer due",
        leftHeadline: "Stock, sales, and godown — one counter.",
        leftFeature1: "POS in BOX, PCS, and SQFT",
        leftFeature2: "Lot stock by batch, shade, grade",
        leftFeature3: "Customer due from the ledger",
        leftFooter: "Showroom · Godown · Accounts",
    };
});

const fieldClass =
    "h-8 rounded-[4px] border-[#D9D1C4] bg-[#FFFcf8] text-[14px] text-[#1C1916] shadow-none placeholder:text-[#6B645B] focus-visible:border-[#B8AD9C] focus-visible:ring-[2px] focus-visible:ring-[#1F6B5A]";
</script>

<template>
    <div
        class="flex min-h-svh bg-[#F3EFE8] text-[#1C1916]"
        style="
            font-family:
                &quot;IBM Plex Sans&quot;, &quot;Noto Sans Bengali&quot;,
                ui-sans-serif, system-ui, sans-serif;
        "
    >
        <Head :title="t.title">
            <link
                rel="stylesheet"
                href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600|noto-sans-bengali:400,500"
            />
        </Head>

        <!-- Left Umber Panel -->
        <aside
            class="relative hidden w-[42%] flex-col justify-between border-r border-[#2A2622] bg-[#1C1916] px-10 py-8 text-[#F3EFE8] lg:flex"
        >
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.06]"
                style="
                    background-image:
                        linear-gradient(#b44422 1px, transparent 1px),
                        linear-gradient(90deg, #b44422 1px, transparent 1px);
                    background-size: 40px 40px;
                "
            />
            <div class="absolute inset-y-0 left-0 w-[3px] bg-[#B44422]" />

            <div class="relative flex items-center gap-3">
                <KilnMark class="size-7 text-[#D36A48]" />
                <span class="text-[15px] font-semibold tracking-tight">
                    {{ page.props.name }}
                </span>
            </div>

            <div class="relative max-w-[290px]">
                <p class="text-[22px] leading-[1.25] font-semibold">
                    {{ t.leftHeadline }}
                </p>
                <ul class="mt-8 flex flex-col gap-3 text-[13px] text-[#C4B8A8]">
                    <li class="flex items-center gap-3">
                        <span class="h-px w-4 bg-[#B44422]" />
                        {{ t.leftFeature1 }}
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="h-px w-4 bg-[#B44422]" />
                        {{ t.leftFeature2 }}
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="h-px w-4 bg-[#B44422]" />
                        {{ t.leftFeature3 }}
                    </li>
                </ul>
            </div>

            <p class="relative text-[12px] text-[#C4B8A8]">
                {{ t.leftFooter }}
            </p>
        </aside>

        <!-- Main Form Area -->
        <main
            class="flex flex-1 flex-col items-center justify-center px-6 py-10"
        >
            <div class="mb-8 flex items-center gap-2 lg:hidden">
                <KilnMark class="size-6 text-[#B44422]" />
                <span class="text-[15px] font-semibold">
                    {{ page.props.name }}
                </span>
            </div>

            <div class="w-full max-w-[360px]">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-[18px] leading-[1.3] font-semibold">
                            {{ t.title }}
                        </h1>
                        <p class="mt-1 text-[13px] text-[#6B645B]">
                            {{ t.subtitle }}
                        </p>
                    </div>

                    <!-- Language Switcher -->
                    <div class="flex items-center text-[12px] text-[#6B645B]">
                        <button
                            type="button"
                            @click="locale = 'en'"
                            class="px-1 py-0.5 font-medium transition-colors"
                            :class="
                                locale === 'en'
                                    ? 'text-[#B44422] underline underline-offset-4'
                                    : 'hover:text-[#1C1916]'
                            "
                        >
                            EN
                        </button>
                        <span class="mx-1 text-[#B8AD9C]">|</span>
                        <button
                            type="button"
                            @click="locale = 'bn'"
                            class="px-1 py-0.5 font-medium transition-colors"
                            :class="
                                locale === 'bn'
                                    ? 'text-[#B44422] underline underline-offset-4'
                                    : 'hover:text-[#1C1916]'
                            "
                        >
                            বাং
                        </button>
                    </div>
                </div>

                <p
                    v-if="status"
                    class="mt-4 text-[13px] font-medium text-[#2F7D4A]"
                >
                    {{ status }}
                </p>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="mt-6 flex flex-col gap-3"
                >
                    <div class="flex flex-col gap-1.5">
                        <label
                            for="email"
                            class="text-[12px] font-medium text-[#6B645B]"
                        >
                            {{ t.emailLabel }}
                        </label>
                        <Input
                            id="email"
                            v-model="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="username"
                            :placeholder="t.emailPlaceholder"
                            :class="fieldClass"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between gap-3">
                            <label
                                for="password"
                                class="text-[12px] font-medium text-[#6B645B]"
                            >
                                {{ t.passwordLabel }}
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-[12px] font-medium text-[#8F3419] hover:underline"
                                :tabindex="5"
                            >
                                {{ t.forgotPassword }}
                            </Link>
                        </div>
                        <PasswordInput
                            id="password"
                            v-model="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            :placeholder="t.passwordLabel"
                            :class="fieldClass"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <label
                        for="remember"
                        class="flex items-center gap-2 text-[13px]"
                    >
                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            :tabindex="3"
                            class="size-4 rounded-[4px] border-[#D9D1C4] accent-[#B44422]"
                        />
                        {{ t.rememberMe }}
                    </label>

                    <button
                        type="submit"
                        class="mt-1 inline-flex h-8 w-full items-center justify-center gap-2 rounded-[4px] bg-[#B44422] text-[13px] font-medium text-white duration-100 hover:bg-[#97381C] focus-visible:ring-[2px] focus-visible:ring-[#1F6B5A] focus-visible:outline-none disabled:opacity-40"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" />
                        {{ t.signInButton }}
                    </button>
                </Form>

                <!-- One-Click Demo Role Accounts Switcher -->
                <div
                    class="mt-6 rounded-[6px] border border-[#D9D1C4] bg-[#FFFCF8] p-3"
                >
                    <div
                        class="flex items-center justify-between text-[11px] text-[#6B645B]"
                    >
                        <span class="font-semibold uppercase tracking-wider">{{
                            t.demoTitle
                        }}</span>
                        <span class="text-[#8F3419] font-medium">{{
                            t.demoClick
                        }}</span>
                    </div>

                    <div class="mt-2.5 grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="selectRole('admin')"
                            class="flex flex-col items-start rounded-[4px] border p-2 text-left transition-all hover:bg-[#F7F1E8]"
                            :class="
                                selectedRole === 'admin'
                                    ? 'border-[#B44422] bg-[#F6E4DC]/50'
                                    : 'border-[#D9D1C4] bg-[#FFFcf8]'
                            "
                        >
                            <div
                                class="flex items-center justify-between w-full"
                            >
                                <span
                                    class="text-[12px] font-semibold text-[#1C1916]"
                                    >{{ t.adminRole }}</span
                                >
                                <span
                                    v-if="selectedRole === 'admin'"
                                    class="size-1.5 rounded-full bg-[#B44422]"
                                />
                            </div>
                            <span class="mt-0.5 text-[11px] text-[#6B645B]">{{
                                t.adminDesc
                            }}</span>
                            <code class="mt-1 text-[10px] text-[#8F3419]"
                                >test@example.com</code
                            >
                        </button>

                        <button
                            type="button"
                            @click="selectRole('sales_shop')"
                            class="flex flex-col items-start rounded-[4px] border p-2 text-left transition-all hover:bg-[#F7F1E8]"
                            :class="
                                selectedRole === 'sales_shop'
                                    ? 'border-[#B44422] bg-[#F6E4DC]/50'
                                    : 'border-[#D9D1C4] bg-[#FFFcf8]'
                            "
                        >
                            <div
                                class="flex items-center justify-between w-full"
                            >
                                <span
                                    class="text-[12px] font-semibold text-[#1C1916]"
                                    >{{ t.salesRole }}</span
                                >
                                <span
                                    v-if="selectedRole === 'sales_shop'"
                                    class="size-1.5 rounded-full bg-[#B44422]"
                                />
                            </div>
                            <span class="mt-0.5 text-[11px] text-[#6B645B]">{{
                                t.salesDesc
                            }}</span>
                            <code class="mt-1 text-[10px] text-[#8F3419]"
                                >sales@example.com</code
                            >
                        </button>
                    </div>
                </div>

                <div
                    class="mt-6 flex items-center justify-center text-[12px] text-[#6B645B]"
                >
                    <span>TileGridERP · Single Showroom & Godown</span>
                </div>
            </div>
        </main>
    </div>
</template>
