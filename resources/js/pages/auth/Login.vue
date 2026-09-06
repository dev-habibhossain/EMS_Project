<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import KilnMark from '@/components/KilnMark.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const page = usePage();

const fieldClass =
    'h-8 rounded-[4px] border-[#D9D1C4] bg-[#FFFcf8] text-[14px] text-[#1C1916] shadow-none placeholder:text-[#6B645B] focus-visible:border-[#B8AD9C] focus-visible:ring-[2px] focus-visible:ring-[#1F6B5A]';
</script>

<template>
    <div
        class="flex min-h-svh bg-[#F3EFE8] text-[#1C1916]"
        style="font-family: 'IBM Plex Sans', 'Noto Sans Bengali', ui-sans-serif, system-ui, sans-serif"
    >
        <Head title="Sign in">
            <link
                rel="stylesheet"
                href="https://fonts.bunny.net/css?family=ibm-plex-sans:400,500,600|noto-sans-bengali:400,500"
            />
        </Head>

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

            <div class="relative max-w-[280px]">
                <p class="text-[22px] leading-[1.25] font-semibold">
                    Stock, sales, and godown — one counter.
                </p>
                <ul class="mt-8 flex flex-col gap-3 text-[13px] text-[#C4B8A8]">
                    <li class="flex items-center gap-3">
                        <span class="h-px w-4 bg-[#B44422]" />
                        POS in BOX, PCS, and SQFT
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="h-px w-4 bg-[#B44422]" />
                        Lot stock by batch, shade, grade
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="h-px w-4 bg-[#B44422]" />
                        Customer due from the ledger
                    </li>
                </ul>
            </div>

            <p class="relative text-[12px] text-[#C4B8A8]">
                Showroom · Godown · Accounts
            </p>
        </aside>

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
                <h1 class="text-[18px] leading-[1.3] font-semibold">
                    Staff sign in
                </h1>
                <p class="mt-1 text-[13px] text-[#6B645B]">
                    Use your shop email and password.
                </p>

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
                            Email
                        </label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="username"
                            placeholder="email@example.com"
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
                                Password
                            </label>
                            <Link
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-[12px] font-medium text-[#8F3419] hover:underline"
                                :tabindex="5"
                            >
                                Forgot password?
                            </Link>
                        </div>
                        <PasswordInput
                            id="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            placeholder="Password"
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
                        Remember me
                    </label>

                    <button
                        type="submit"
                        class="mt-1 inline-flex h-8 w-full items-center justify-center gap-2 rounded-[4px] bg-[#B44422] text-[13px] font-medium text-white duration-100 hover:bg-[#97381C] focus-visible:ring-[2px] focus-visible:ring-[#1F6B5A] focus-visible:outline-none disabled:opacity-40"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" />
                        Sign in
                    </button>
                </Form>

                <p class="mt-8 text-[12px] text-[#6B645B]">
                    <span class="font-medium text-[#1C1916]">EN</span>
                    <span class="mx-1.5 text-[#B8AD9C]">|</span>
                    <span>বাং</span>
                </p>
            </div>
        </main>
    </div>
</template>
