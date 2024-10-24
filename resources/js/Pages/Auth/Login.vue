<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    np: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Login" />
    <div class="h-screen overflow-hidden">
        <div
            class="h-full w-screen flex items-center bg-gradient-to-br from-slate-100 to-slate-200"
        >
            <div
                class="flex flex-col w-full mx-auto p-8 md:p-10 2xl:p-12 3xl:p-14 bg-[#ffffff] rounded-2xl shadow-xl max-w-xl"
            >
                <div class="flex flex-row gap-3 pb-4 items-center">
                    <div>
                        <img src="/img/logo.png" width="50" alt="Logo" />
                    </div>
                    <h1
                        class="text-3xl font-bold text-[#4B5563] text-[#4B5563]"
                    >
                        Label Generator
                    </h1>
                </div>
                <div class="text-sm font-light text-[#6B7280] pb-8">
                    Selamat datang, silahkan login untuk memulai.
                </div>
                <form @submit.prevent="submit" class="flex flex-col">
                    <div class="pb-2">
                        <label
                            for="npUser"
                            class="block mb-2 text-sm font-medium text-[#111827]"
                            >Nomor Pegawai</label
                        >
                        <div class="relative text-gray-400">
                            <span
                                class="absolute inset-y-0 left-0 flex items-center p-1 pl-3"
                                ><svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="size-6"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                    />
                                </svg>
                            </span>
                            <input
                                type="text"
                                name="npUser"
                                id="npUser"
                                v-model="form.np"
                                class="pl-12 mb-2 bg-gray-50 text-gray-600 border focus:border-transparent border-gray-300 sm:text-sm rounded-lg ring ring-transparent focus:ring-1 focus:outline-none focus:ring-gray-400 block w-full p-2.5 rounded-l-lg py-3 px-4"
                                placeholder="Nomor Pegawai"
                                autocomplete="off"
                            />
                        </div>
                    </div>
                    <div class="pb-6">
                        <label
                            for="password"
                            class="block mb-2 text-sm font-medium text-[#111827]"
                            >Password</label
                        >
                        <div class="relative text-gray-400">
                            <span
                                class="absolute inset-y-0 left-0 flex items-center p-1 pl-3"
                                ><svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="lucide lucide-square-asterisk"
                                >
                                    <rect
                                        width="18"
                                        height="18"
                                        x="3"
                                        y="3"
                                        rx="2"
                                    ></rect>
                                    <path d="M12 8v8"></path>
                                    <path d="m8.5 14 7-4"></path>
                                    <path d="m8.5 10 7 4"></path></svg
                            ></span>
                            <input
                                v-model="form.password"
                                type="password"
                                name="password"
                                id="password"
                                placeholder="••••••••••"
                                class="pl-12 mb-2 bg-gray-50 text-gray-600 border focus:border-transparent border-gray-300 sm:text-sm rounded-lg ring ring-transparent focus:ring-1 focus:outline-none focus:ring-gray-400 block w-full p-2.5 rounded-l-lg py-3 px-4"
                                autocomplete="new-password"
                            />
                        </div>
                    </div>
                    <button
                        type="submit"
                        class="w-full text-[#FFFFFF] bg-[#4F46E5] focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center mb-6"
                    >
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
