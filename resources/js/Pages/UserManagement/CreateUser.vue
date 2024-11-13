<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm} from "@inertiajs/vue3";
import { ref, defineProps  } from "vue";

const props = defineProps({
    listTeam : Object,
})

const userForm = useForm({
    npUser: "",
    password: "Peruri",
    roleUser: 0,
    team: 1,
});

const submit = () => {
    router.post("/create-user",userForm, {
        onSuccess: () => {
            userForm.reset()
        }
    });
};

const isDefaultPassword = ref(true);
const showPassword = ref(false);

const useDefaultPassword = () => {
    if (isDefaultPassword.value == true) {
        userForm.password = "Peruri" + userForm.npUser;
    } else {
        userForm.password = "";
    }
};

const changeStateDefaultPassword = () => {
    isDefaultPassword.value = !isDefaultPassword.value;
    useDefaultPassword();
}

</script>

<template>
    <Head title="Create User"/>
    <AuthenticatedLayout>
        <div
            class="w-full max-w-xl bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mx-auto mt-24"
        >
        <!-- Title -->
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Create User</h2>

            <form @submit.prevent="submit" class="flex flex-col">
                <!-- Input NP -->
                <input
                    v-model="userForm.npUser"
                    v-on:keyup="useDefaultPassword"
                    maxlength="4"
                    type="text"
                    class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border-0 rounded-md p-2 mb-4 focus:bg-gray-200 dark:focus:bg-gray-600 focus:outline-none focus:ring-1 focus:ring-blue-500 transition ease-in-out duration-150"
                    placeholder="NP User"
                />

                <!-- Select Role User -->
                <select
                    v-model="userForm.roleUser"
                    class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border-0 rounded-md p-2 mb-4 focus:bg-gray-200 dark:focus:bg-gray-600 focus:outline-none focus:ring-1 focus:ring-blue-500 transition ease-in-out duration-150"
                >
                    <option value="0">User</option>
                    <option value="1">Admin</option>
                </select>

                <!-- Select Team / Workstation User -->
                <select
                    v-model="userForm.team"
                    class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border-0 rounded-md p-2 mb-4 focus:bg-gray-200 dark:focus:bg-gray-600 focus:outline-none focus:ring-1 focus:ring-blue-500 transition ease-in-out duration-150"
                >
                    <option v-for="team in props.listTeam" :value="team.id">{{ team.workstation }}</option>
                </select>

                <div class="w-full relative">

                    <!-- Form Password -->
                    <input
                        v-model="userForm.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="bg-gray-100 dark:bg-gray-700 w-full text-gray-800 dark:text-gray-200 border-0 rounded-md p-2 mb-4 focus:bg-gray-200 dark:focus:bg-gray-600 focus:outline-none focus:ring-1 focus:ring-blue-500 transition ease-in-out duration-150"
                        placeholder="Password"
                    />

                    <!-- Show Button -->
                    <button
                        type="button"
                        class="absolute right-0 pt-2.5 pr-4"
                        v-on:click="showPassword = !showPassword"
                        v-if="showPassword"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5 text-slate-500 dark:text-slate-400"
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
                        v-on:click="showPassword = !showPassword"
                        v-else
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            class="size-5 text-slate-500 dark:text-slate-400"
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

                <!-- Checkbox Default Password -->
                <div class="flex gap-3 items-center px-1">
                    <input
                        id="isDefPassword"
                        type="checkbox"
                        checked
                        v-on:click="changeStateDefaultPassword"
                        class="size-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                    />
                    <strong class="dark:text-gray-200">Default Password</strong>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="bg-gradient-to-r from-indigo-500 to-blue-500 text-white font-bold py-2 px-4 rounded-md mt-4 hover:bg-indigo-600 hover:to-blue-600 transition ease-in-out duration-150 dark:from-indigo-600 dark:to-blue-600 dark:hover:from-indigo-700 dark:hover:to-blue-700"
                >
                    Submit
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
