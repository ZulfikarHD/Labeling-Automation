<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const formPassword = useForm({
    oldPassword: "",
    newPassword: "",
    confirmPassword: "",
});

const showOldPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    router.post(route('changePassword.update'),formPassword,{
        onSuccess: () => {
            formPassword.reset();
        }
    });
}

</script>

<template>
    <Head title="Change Password" />

    <AuthenticatedLayout>
        <div
            class="w-full max-w-xl bg-white rounded-lg shadow-md p-6 mx-auto mt-24"
        >
        <!-- Title -->
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Ganti Password</h2>

            <form @submit.prevent="submit" class="flex flex-col">
                <!-- Old Password -->
                <div class="w-full relative">
                    <input
                        v-model="formPassword.oldPassword"
                        :type="showOldPassword ? 'text' : 'password'"
                        class="bg-gray-100 w-full text-gray-800 border-0 rounded-md p-2 mb-4 focus:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 transition ease-in-out duration-150"
                        placeholder="Password Lama"
                    />

                    <!-- Show Button -->
                    <button
                        type="button"
                        class="absolute right-0 pt-2.5 pr-4"
                        v-on:click="showOldPassword = !showOldPassword"
                        v-if="showOldPassword"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5 text-slate-500"
                        >
                            <path
                                d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"
                            />
                            <path
                                fill-rule="evenodd"
                                d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <!-- Hide Button -->
                    <button
                        type="button"
                        class="absolute right-0 pt-2.5 pr-4"
                        v-on:click="showOldPassword = !showOldPassword"
                        v-else
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5 text-slate-500"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.745-1.745a10.029 10.029 0 0 0 3.3-4.38 1.651 1.651 0 0 0 0-1.185A10.004 10.004 0 0 0 9.999 3a9.956 9.956 0 0 0-4.744 1.194L3.28 2.22ZM7.752 6.69l1.092 1.092a2.5 2.5 0 0 1 3.374 3.373l1.091 1.092a4 4 0 0 0-5.557-5.557Z"
                                clip-rule="evenodd"
                            />
                            <path
                                d="m10.748 13.93 2.523 2.523a9.987 9.987 0 0 1-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 0 1 0-1.186A10.007 10.007 0 0 1 2.839 6.02L6.07 9.252a4 4 0 0 0 4.678 4.678Z"
                            />
                        </svg>
                    </button>
                </div>

                <!-- New Password -->
                <div class="w-full relative">
                    <input
                        v-model="formPassword.newPassword"
                        :type="showNewPassword ? 'text' : 'password'"
                        class="bg-gray-100 w-full text-gray-800 border-0 rounded-md p-2 mb-4 focus:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 transition ease-in-out duration-150"
                        placeholder="Password Baru"
                    />

                    <!-- Show Button -->
                    <button
                        type="button"
                        class="absolute right-0 pt-2.5 pr-4"
                        v-on:click="showNewPassword = !showNewPassword"
                        v-if="showNewPassword"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5 text-slate-500"
                        >
                            <path
                                d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"
                            />
                            <path
                                fill-rule="evenodd"
                                d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <!-- Hide Button -->
                    <button
                        type="button"
                        class="absolute right-0 pt-2.5 pr-4"
                        v-on:click="showNewPassword = !showNewPassword"
                        v-else
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5 text-slate-500"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.745-1.745a10.029 10.029 0 0 0 3.3-4.38 1.651 1.651 0 0 0 0-1.185A10.004 10.004 0 0 0 9.999 3a9.956 9.956 0 0 0-4.744 1.194L3.28 2.22ZM7.752 6.69l1.092 1.092a2.5 2.5 0 0 1 3.374 3.373l1.091 1.092a4 4 0 0 0-5.557-5.557Z"
                                clip-rule="evenodd"
                            />
                            <path
                                d="m10.748 13.93 2.523 2.523a9.987 9.987 0 0 1-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 0 1 0-1.186A10.007 10.007 0 0 1 2.839 6.02L6.07 9.252a4 4 0 0 0 4.678 4.678Z"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Confirm Password -->
                <div class="w-full relative">
                    <input
                        v-model="formPassword.confirmPassword"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="bg-gray-100 w-full text-gray-800 border-0 rounded-md p-2 mb-4 focus:bg-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 transition ease-in-out duration-150"
                        placeholder="Konfirmasi Password Baru"
                    />

                    <!-- Show Button -->
                    <button
                        type="button"
                        class="absolute right-0 pt-2.5 pr-4"
                        v-on:click="showConfirmPassword = !showConfirmPassword"
                        v-if="showConfirmPassword"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5 text-slate-500"
                        >
                            <path
                                d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"
                            />
                            <path
                                fill-rule="evenodd"
                                d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <!-- Hide Button -->
                    <button
                        type="button"
                        class="absolute right-0 pt-2.5 pr-4"
                        v-on:click="showConfirmPassword = !showConfirmPassword"
                        v-else
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5 text-slate-500"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.745-1.745a10.029 10.029 0 0 0 3.3-4.38 1.651 1.651 0 0 0 0-1.185A10.004 10.004 0 0 0 9.999 3a9.956 9.956 0 0 0-4.744 1.194L3.28 2.22ZM7.752 6.69l1.092 1.092a2.5 2.5 0 0 1 3.374 3.373l1.091 1.092a4 4 0 0 0-5.557-5.557Z"
                                clip-rule="evenodd"
                            />
                            <path
                                d="m10.748 13.93 2.523 2.523a9.987 9.987 0 0 1-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 0 1 0-1.186A10.007 10.007 0 0 1 2.839 6.02L6.07 9.252a4 4 0 0 0 4.678 4.678Z"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="bg-gradient-to-r from-indigo-500 to-blue-500 text-white font-bold py-2 px-4 rounded-md mt-4 hover:bg-indigo-600 hover:to-blue-600 transition ease-in-out duration-150"
                >
                    Submit
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
