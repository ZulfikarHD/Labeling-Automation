<script setup>
import { inject, ref } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import NavigateBackButton from "@/Components/NavigateBackButton.vue";

const swal = inject('$swal');
const isLoading = ref(false);
const errorPo = ref("");
const errorObc = ref("");
const confirmationMessage = ref("tests");

const props = defineProps({
    workstation: Object,
    currentTeam: Number,
});

const seri = ref("");

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
    team: props.currentTeam,
});

const fetchData = () => {
    isLoading.value = true;
    errorPo.value = "";

    axios.get(`/api/order-besar/register-no-po/${form.po}`).then((response) => {
        const data = response.data;
        form.obc = data.no_obc;
        form.jml_lembar = data.rencet;
        form.jml_rim = Math.ceil(data.rencet / 500);
        form.end_rim = Math.max(1, Math.floor(data.rencet / 500 / 2));
        form.inschiet = (data.rencet % 1000 === 0) ? data.rencet % 500 : data.rencet % 1000;

        updateConfirmationMessage();
    }).catch(() => {
        errorPo.value = "Nomor PO Tidak Ditemukan";
        resetForm();
    }).finally(() => {
        isLoading.value = false;
    });
};

const debounce = (func, delay) => {
    let timeout;
    return () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this), delay);
    };
};

const debouncedFetchData = debounce(fetchData, 500);

const calcEndRim = () => {
    form.end_rim = form.jml_lembar > 500
        ? Math.max(1, parseInt(form.start_rim) + Math.floor(form.jml_lembar / 500 / 2 - 1))
        : Math.max(1, parseInt(form.start_rim));
    form.jml_rim = Math.ceil(form.jml_lembar / 500);
    form.inschiet = form.jml_lembar % 1000 === 0
        ? form.jml_lembar - (Math.floor(form.jml_lembar / 500) * 500)
        : form.jml_lembar % 1000;
};

const cekSpec = () => {
    if (typeof form.obc === 'string' && form.obc.length >= 3) {
        const firstThreeLetters = form.obc.substring(0, 3);
        if (!/^[a-zA-Z]+$/.test(firstThreeLetters)) {
            errorObc.value = "Mohon masukan kode Daerah ex.'PST'";
        } else {
            updateConfirmationMessage();
        }
    }
};

const updateConfirmationMessage = () => {
    const firstThreeLetters = form.obc.substring(0, 3);
    let daerahOrder;
    if (firstThreeLetters === "PST") {
        daerahOrder = "<span class='text-blue-500 font-semibold'>Pusat</span>";
    } else {
        daerahOrder = "<span class='text-red-500 font-semibold'>Daerah</span>";
    }

    let seriColor;
    const seriValue = form.obc.substring(5, 4);
    if (seriValue == 3) {
        seri.value = `<span class='text-blue-500 font-semibold'>${seriValue}</span>`;
    } else if (seriValue == 2) {
        seri.value = `<span class='text-green-500 font-semibold'>${seriValue}</span>`;
    } else {
        seri.value = `<span class='text-red-500 font-semibold'>1</span>`;
    }

    confirmationMessage.value = `Order ${daerahOrder} Seri ${seri.value} ?`;
};

const resetForm = () => {
    form.obc = null;
    form.jml_lembar = 0;
    form.jml_rim = 0;
    form.end_rim = 1;
    form.inschiet = 0;
};

function submit() {
    swal.fire({
        html: confirmationMessage.value,
        title: 'Periksa Kembali Spesifikasi',
        text: "test",
        icon: 'warning',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Buat Label',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post("/api/order-besar/register-no-po", form, {
                onSuccess: () => {
                    swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Label Berhasil Dibuat',
                    });
                    form.reset();
                }
            });
        }
    });
}
</script>
<template>
    <!-- Loading Indicator -->
    <div class="bg-black bg-opacity-40 w-screen h-screen absolute z-50 flex justify-center items-center" v-if="isLoading">
        <div class="rounded-lg p-4 flex flex-col gap-2 justify-center items-center">
            <svg class="animate-spin h-10 w-10 brightness-110" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25 drop-shadow-md shadow-md text-blue-50" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75 shadow-md text-blue-500" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-white font-semibold animate-pulse">Sedang Memproses...</span>
        </div>
    </div>

    <!-- Content -->
    <AuthenticatedLayout>
        <div class="py-12 px-4">
            <form @submit.prevent="submit" method="post">
                <div class="flex flex-col justify-center gap-6 mx-auto mt-12 w-fit">
                    <!-- Nomor PO -->
                    <div>
                        <InputLabel for="po" value="Nomor PO" class="text-3xl font-extrabold text-center" />
                        <TextInput id="po" ref="po" v-model="form.po" type="number" class="block w-full px-8 py-2 mt-2 text-xl text-center border border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500" autocomplete="po" placeholder="Nomor Produksi" @input="debouncedFetchData" required />
                        <p v-if="errorPo" class="text-red-500 text-lg text-center mt-4 font-medium">{{ errorPo }}</p>
                    </div>

                    <!-- Assigned Team -->
                    <div>
                        <InputLabel for="team" value="Tim Periksa" class="text-3xl font-extrabold text-center" />
                        <select id="team" ref="team" v-model="form.team" class="block w-full px-8 py-2 mt-2 text-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option v-for="team in workstation" :key="team.id" :value="team.id">
                                {{ team.workstation }}
                            </option>
                        </select>
                    </div>

                    <div class="flex justify-between gap-6 w-fit">
                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel for="obc" value="Nomor OBC" class="text-3xl font-extrabold text-center" />
                            <TextInput id="obc" ref="obc" v-model="form.obc" type="text" @input="cekSpec" class="block w-full px-8 py-2 mt-2 text-xl text-center border border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500" autocomplete="obc" placeholder="Order Bea Cukai" required />
                            <p v-if="errorObc" class="text-red-500 text-sm text-center mt-4 font-medium">{{ errorObc }}</p>
                        </div>

                        <!-- Jumlah Cetak -->
                        <div>
                            <InputLabel for="jml_lembar" value="Jumlah Cetak" class="text-3xl font-extrabold text-center" />
                            <TextInput id="jml_lembar" ref="jml_lembar" v-model="form.jml_lembar" type="number" class="block w-full px-8 py-2 mt-2 text-xl text-center border border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500" autocomplete="jml_lembar" placeholder="Lembar" min="1" @input="calcEndRim" required />
                        </div>

                        <!-- Inschiet -->
                        <div>
                            <InputLabel for="inschiet" value="Inschiet" class="text-3xl font-extrabold text-center" />
                            <TextInput id="inschiet" ref="inschiet" v-model="form.inschiet" type="number" class="block w-full px-8 py-2 mt-2 text-xl text-center border border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500" autocomplete="inschiet" placeholder="Lembar" @input="calcEndRim" />
                        </div>

                        <!-- Jumlah Rim -->
                        <div>
                            <InputLabel for="jml_rim" value="Jumlah Rim" class="text-3xl font-extrabold text-center" />
                            <TextInput id="jml_rim" ref="jml_rim" v-model="form.jml_rim" type="number" class="block w-full px-8 py-2 mt-2 text-xl text-center bg-slate-200 border border-gray-300 rounded-md" autocomplete="jml_rim" placeholder="RIM" min="1" required />
                        </div>
                    </div>
                    <h4 class="text-3xl font-semibold text-center">Nomor RIM</h4>
                    <div class="flex justify-center gap-6 w-full">
                        <!-- Start RIM -->
                        <div>
                            <InputLabel for="start_rim" value="Dari" class="text-3xl font-extrabold text-center" />
                            <TextInput id="start_rim" ref="start_rim" v-model="form.start_rim" type="number" class="block w-full px-8 py-2 mt-2 text-xl text-center border border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500" autocomplete="start_rim" @input="calcEndRim" min="1" />
                        </div>

                        <!-- End Rim -->
                        <div>
                            <InputLabel for="end_rim" value="Sampai" class="text-3xl font-extrabold text-center" />
                            <TextInput id="end_rim" ref="end_rim" v-model="form.end_rim" type="number" class="block w-full px-8 py-2 mt-2 text-xl text-center bg-slate-200 border border-gray-300 rounded-md" autocomplete="end_rim" disabled min="1" />
                        </div>
                    </div>
                </div>
                <div class="flex justify-center gap-6 mx-auto w-fit">
                    <button type="submit" class="flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start mt-11 hover:shadow-lg transition-shadow duration-300">
                        <span class="text-xl font-bold text-yellow-50">Buat Label</span>
                    </button>
                    <Link :href="route('orderBesar.registerNomorPo')" class="text-xl font-bold text-violet-50 flex justify-center px-8 py-4 mx-auto w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start mt-11 hover:shadow-lg transition-shadow duration-300">
                        Clear
                    </Link>
                </div>
            </form>
        </div>
        <div class="flex justify-center w-full">
            <div class="flex gap-6">
                <!-- Home Button -->
                <Link :href="route('dashboard')" class="text-xl font-extrabold text-blue-50 w-fit py-3 px-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl text-start drop-shadow-md shadow-md flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                        <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                        <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
                    </svg>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
