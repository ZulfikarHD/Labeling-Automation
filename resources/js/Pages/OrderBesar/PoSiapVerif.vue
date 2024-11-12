<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import NavigateBackButton from '@/Components/NavigateBackButton.vue';
import { ref } from 'vue';
import { Home, Check, Clock, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    products: Object,
    teamList: Object,
    crntTeam: Object,
});

const listProduct = ref(props.products);

const form = useForm({
    team: props.crntTeam,
});

const changeTeam = () => {
    axios.get('/api/order-besar/verif/'+form.team).then((res) => {
        listProduct.value = res.data;
    });
}
</script>

<template>
    <Head title="Siap Periksa" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
            <!-- Team Selection -->
            <div class="max-w-md mx-auto mb-12">
                <InputLabel for="team" value="Pilih Tim" class="text-2xl font-bold text-slate-800 mb-3" />
                <select
                    id="team"
                    v-model="form.team"
                    @change="changeTeam"
                    class="w-full px-4 py-3 text-lg bg-white border rounded-xl border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-colors"
                >
                    <option v-for="teams in props.teamList" :value="teams.id">
                        {{ teams.workstation }}
                    </option>
                </select>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <!-- Table Header -->
                <div class="p-6 border-b border-slate-200">
                    <h1 class="text-2xl font-bold text-slate-800">Daftar Produk Siap Periksa</h1>
                    <p class="mt-1 text-sm text-slate-500">Daftar produk yang siap untuk diperiksa oleh tim</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 text-left">No. PO</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 text-left">OBC</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 text-center">Jenis</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 text-center">No. Rim</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 text-center">Status</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 text-center">Waktu</th>
                                <th class="px-6 py-4 text-sm font-semibold text-slate-600 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr v-for="product in listProduct" class="hover:bg-slate-100 transition-colors">
                                <td class="px-6 py-4 text-sm text-slate-600">{{ product.no_po }}</td>
                                <td class="px-6 py-4 text-sm font-medium" :class="{'text-red-600': product.no_obc.substr(4,1) == 3, 'text-blue-600': product.no_obc.substr(4,1) != 3}">
                                    {{ product.no_obc }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 text-center">{{ product.type }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600 text-center">{{ product.start_rim }} - {{ product.end_rim }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="product.status == 1" class="inline-flex items-center px-3 py-1 text-sm font-medium text-yellow-900 bg-yellow-300 rounded-lg">
                                        Sedang Diperiksa
                                    </span>
                                    <span v-else class="inline-flex items-center px-3 py-1 text-sm font-medium text-slate-50 bg-slate-600 rounded-lg">
                                        Siap Diperiksa
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 text-center">{{ product.created_at }}</td>
                                <td class="px-6 py-4 text-center">
                                    <Link
                                        :href="route('orderBesar.cetakLabel', {team : form.team, id : product.id})"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                                    >
                                        <ArrowRight class="w-4 h-4" />
                                        Pilih
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
