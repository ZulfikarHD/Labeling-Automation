
<script setup>
import { inject, ref } from "vue";
import ContentLayout from "@/Layouts/ContentLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import NavigateBackButton from "@/Components/NavigateBackButton.vue";

// Injects the swal alert system from the parent component or root instance.
const swal = inject('$swal');

// Defines component props with default values and types.
const props = defineProps({
    workstation: Object,
});

// Initializes form data using the useForm hook from Inertia.js.
const form = useForm({
    po: null,
    obc: "",
    jml_rim: 0,
    jml_lembar: 0,
    seri: 1,
    start_rim: 1,
    produk: "PCHT",
    end_rim: 40,
    inschiet: 0,
    team: 1,
});

const isLoading = ref(false)

// Fetches data from the server based on the PO number and updates form data accordingly.
const fetchData = () => {
    isLoading.value = true; // Start loading indicator

    axios.get("/api/gen-labels/non-personal/" + form.po).then((response) => {
        console.log(response.data.no_obc);
        // Update form data with the response
        form.obc = response.data.no_obc;
        form.jml_lembar = response.data.rencet;
        form.jml_rim = Math.ceil(response.data.rencet / 500);
        form.end_rim = Math.floor(response.data.rencet / 500 / 2);

        // Calculate inschiet based on the remaining sheets
        const jumlahInschiet = response.data.rencet % 500;
        form.inschiet = (response.data.rencet % 1000 === 0) ? jumlahInschiet : response.data.rencet % 1000;
    }).finally(() => {
        isLoading.value = false; // Stop loading indicator
    });
};

// Calculates the end rim and inschiet based on the total number of sheets.
const calcEndRim = () => {
    // If the number of sheets is more than 500, calculate the end rim
    if (form.jml_lembar > 500) {
        form.end_rim = parseInt(form.start_rim) + parseInt(Math.floor(form.jml_lembar / 500 / 2 - 1));
    } else {
        form.end_rim = parseInt(form.start_rim);
    }

    // Calculate the number of rims needed
    form.jml_rim = Math.ceil(form.jml_lembar / 500);

    // Calculate the remaining sheets after full rims
    const sisaInschiet = form.jml_lembar - (Math.floor(form.jml_lembar / 500) * 500);

    // If the total number of sheets is a multiple of 1000, set inschiet to remaining sheets
    if (form.jml_lembar % 1000 === 0) {
        form.inschiet = sisaInschiet;
    } else {
        // Otherwise, set inschiet to the remainder when divided by 1000
        form.inschiet = form.jml_lembar % 1000;
    }
};

// Submits the form data to the server and shows a success message on completion.
function submit() {
    // Sends the form data to the server to create a non-personal label
    router.post("/api/gen-labels/non-personal", form, {
        onSuccess: () => {
            // Displays a success message using a SweetAlert popup
            swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Label Berhasil Dibuat',
            });
            // Resets the form fields after successful submission
            form.reset();
        }
    });
}
</script>

<template>
    <div class="bg-slate-800 bg-opacity-40 w-screen h-screen absolute z-50" v-if="isLoading">
        <button type="button" class="bg-indigo-500 top-1/2 left-1/2 mx-auto my-auto  px-4 py-4" disabled>
            <svg class="animate-spin h-5 w-5 mr-3 p-8" viewBox="0 0 24 24">
                <!-- ... -->
            </svg>
            Processing...
        </button>
    </div>
    <ContentLayout>
        <div class="py-12 px-4">
            <form @submit.prevent="submit" method="post">
                <div class="flex flex-col justify-center gap-6 mx-auto mt-12 w-fit">
                    <!-- Nomor PO -->
                    <div>
                        <InputLabel for="po" value="Nomor PO" class="text-3xl font-extrabold text-center" />

                        <TextInput id="po" ref="po" v-model="form.po" type="number"
                            class="block w-full px-8 py-2 mt-2 text-xl text-center" autocomplete="po"
                            placeholder="Production Order" @input="fetchData" required />
                    </div>

                    <!-- Assigned Team -->
                    <div>
                        <InputLabel for="team" value="Team Periksa" class="text-3xl font-extrabold text-center" />

                        <select id="team" ref="team" v-model="form.team"
                            class="block w-full px-8 py-2 mt-2 text-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required>
                            <option v-for="team in workstation" :value="team.id">
                                {{ team.workstation }}
                            </option>
                        </select>
                    </div>

                    <div class="flex justify-between gap-6 w-fit">
                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel for="obc" value="Nomor OBC" class="text-3xl font-extrabold text-center" />

                            <TextInput id="obc" ref="obc" v-model="form.obc" type="text"
                                class="block w-full px-8 py-2 mt-2 text-xl text-center" autocomplete="obc"
                                placeholder="Order Bea Cukai" required />
                        </div>

                        <!-- Jumlah Cetak -->
                        <div>
                            <InputLabel for="jml_lembar" value="Jumlah Cetak"
                                class="text-3xl font-extrabold text-center" />

                            <TextInput id="jml_lembar" ref="jml_lembar" v-model="form.jml_lembar" type="number"
                                class="block w-full px-8 py-2 mt-2 text-xl text-center" autocomplete="jml_lembar"
                                placeholder="Lembar" min="1" @input="calcEndRim" required />
                        </div>

                        <!-- Inschiet -->
                        <div>
                            <InputLabel for="inschiet" value="Inschiet" class="text-3xl font-extrabold text-center" />

                            <TextInput id="inschiet" ref="inschiet" v-model="form.inschiet" type="number"
                                class="block w-full px-8 py-2 mt-2 text-xl text-center" autocomplete="inschiet"
                                placeholder="Lembar" @input="calcEndRim" />
                        </div>

                        <!-- Jumlah Rim -->
                        <div>
                            <InputLabel for="jml_rim" value="Jumlah Rim" class="text-3xl font-extrabold text-center" />

                            <TextInput id="jml_rim" ref="jml_rim" v-model="form.jml_rim" type="number"
                                class="block w-full px-8 py-2 mt-2 text-xl text-center bg-slate-200"
                                autocomplete="jml_rim" placeholder="RIM" min="1" required />
                        </div>
                    </div>
                    <h4 class="text-3xl font-semibold text-center">
                        Nomor RIM
                    </h4>
                    <div class="flex justify-center gap-6 w-full">
                        <!-- Start RIM -->
                        <div>
                            <InputLabel for="start_rim" value="Dari" class="text-3xl font-extrabold text-center" />

                            <TextInput id="start_rim" ref="start_rim" v-model="form.start_rim" type="number"
                                class="block w-full px-8 py-2 mt-2 text-xl text-center" autocomplete="start_rim"
                                @input="calcEndRim" min="1" />
                        </div>

                        <!-- End Rim -->
                        <div>
                            <InputLabel for="end_rim" value="Sampai" class="text-3xl font-extrabold text-center" />

                            <TextInput id="end_rim" ref="end_rim" v-model="form.end_rim" type="number"
                                class="block w-full px-8 py-2 mt-2 text-xl text-center bg-slate-200"
                                autocomplete="end_rim" disabled min="1" />
                        </div>
                    </div>
                </div>
                <div class="flex justify-center gap-6 mx-auto w-fit">
                    <Link :href="route('nonPer.nonPersonal.entryPo.index')"
                        class="text-xl font-bold text-violet-50 flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-11">
                    Clear
                    </Link>
                    <button type="submit"
                        class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11">
                        <span class="text-xl font-bold text-yellow-50">Buat Label</span>
                    </button>
                </div>
            </form>
        </div>
        <div class="flex justify-center w-full">
            <div class="flex gap-6">
                <!-- Back Button -->
                <NavigateBackButton :link="route('nonPer.nonPersonal.pic.index')" />

                <!-- Home Button -->
                <Link :href="route('dashboard')"
                    class="text-xl font-extrabold text-blue-50 w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start drop-shadow-md shadow-md flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                    <path
                        d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                    <path
                        d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
                </svg>
                </Link>
            </div>
        </div>
    </ContentLayout>
</template>
