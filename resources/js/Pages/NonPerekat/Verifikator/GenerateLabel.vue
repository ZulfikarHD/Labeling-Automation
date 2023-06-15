<script setup>
import Modal from '@/Components/Modal.vue'
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link,useForm } from '@inertiajs/vue3';

const props = defineProps({
                product : Object,
                showModal: {
                    type: Boolean,
                    default: false,
                },
            });

const form = useForm({
    po  : props.product.no_po ,
    obc : props.product.no_obc,
    team    : props.product.assigned_team,
    seri    : '',
    jml_rim : '1',
    lbr_ptg : 'Kiri',
    rfid    : '',
});

</script>

<template>
    <Modal :show="showModal" @close="Modal => showModal = !showModal">
        <div class="px-8 py-6">
            <form>
                <div class="pb-4 mb-4 border-b-2 border-slate-400">
                    <h3 class="text-2xl font-semibold text-center text-slate-700">Barang Yang Anda Ambil</h3>
                    <div>
                        <h3 class="text-2xl font-semibold text-center text-slate-700">
                            Berjumlah <span class="text-cyan-600 brightness-110">{{ form.jml_rim }} RIM</span>, Dengan Nomor <span class="text-emerald-600 brightness-110">24,25</span>, Sisiran {{ form.lbr_ptg }}
                        </h3>
                    </div>
                </div>
                <div>
                    <InputLabel for="rfid" value="Silahkan Scan RFID mu" class="text-2xl font-semibold text-center" />

                    <TextInput
                        id="rfid"
                        type="text"
                        class="block w-full mt-4 text-center"
                        v-model="form.rfid"
                        required
                        autofocus
                        autocomplete="rfid"
                    />

                    <InputError class="mt-2" :message="form.errors.rfid" />
                </div>
                <div class="flex flex-row justify-center gap-4">
                    <button type="button" @click="showModal = !showModal" class="flex justify-center px-4 py-2 mt-8 border border-blue-400 shadow-md w-fit bg-inherit rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-blue-500/20">
                        <span class="text-lg font-bold text-blue-500">Cancel</span>
                    </button>
                    <button type="button" class="flex justify-center px-4 py-2 mt-8 shadow-md w-fit bg-gradient-to-r from-blue-400 to-blue-500 rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-blue-500/20">
                        <span class="text-lg font-bold text-indigo-50">Print</span>
                    </button>
                </div>
            </form>
        </div>
    </Modal>
    <ContentLayout>
        <div class="py-8">
            <form>
                <div class="flex flex-col justify-center gap-6 mx-auto w-fit">
                    <!-- Team -->
                    <div class="mx-auto mb-7">
                        <InputLabel for="team" value="Team" class="text-2xl font-extrabold text-center"/>

                        <TextInput
                            id="team"
                            ref="team"
                            v-model="form.team"
                            type="text"
                            class="block px-4 py-2 mt-2 text-lg text-center shadow w-fit bg-slate-300 drop-shadow"
                            autocomplete="team"
                            disabled
                        />
                    </div>
                    <div class="flex justify-between gap-6 mb-6 w-fit">
                        <!-- PO -->
                        <div>
                            <InputLabel for="po" value="Nomor PO" class="text-2xl font-extrabold text-center"/>

                            <TextInput
                                id="po"
                                ref="po"
                                v-model="form.po"
                                type="number"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-300 drop-shadow"
                                autocomplete="po"
                                disabled
                            />
                        </div>

                        <!-- Nomor OBC -->
                        <div>
                            <InputLabel for="obc" value="Nomor OBC" class="text-2xl font-extrabold text-center"/>

                            <TextInput
                                id="obc"
                                ref="obc"
                                v-model="form.obc"
                                type="text"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-300 drop-shadow"
                                autocomplete="obc"
                                disabled
                            />
                        </div>

                        <!-- Seri -->
                        <div>
                            <InputLabel for="seri" value="Seri" class="text-2xl font-extrabold text-center "/>

                            <TextInput
                                id="seri"
                                ref="seri"
                                v-model="form.seri"
                                type="number"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center shadow bg-slate-300 drop-shadow"
                                autocomplete="seri"
                                value="1"
                            />
                        </div>
                    </div>

                    <div class="flex justify-between gap-6 mx-auto w-fit">
                        <!-- Start RIM -->
                        <div>
                            <InputLabel for="jml_rim" value="Jumlah RIM" class="text-2xl font-extrabold text-center"/>

                            <TextInput
                                id="jml_rim"
                                ref="jml_rim"
                                v-model="form.jml_rim"
                                type="number"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center"
                                autocomplete="jml_rim"
                                min="1"
                                max="10"
                            />
                        </div>

                        <!-- End Rim -->
                        <div>
                            <InputLabel for="lbr_ptg" value="Lembar Potong" class="text-2xl font-extrabold text-center"/>

                            <TextInput
                                id="lbr_ptg"
                                ref="lbr_ptg"
                                v-model="form.lbr_ptg"
                                type="text"
                                class="block w-full px-4 py-2 mt-2 text-lg text-center"
                                autocomplete="lbr_ptg"
                                disabled
                            />
                        </div>
                    </div>
                </div>

                <!-- Keypad -->
                <div class="grid grid-cols-2 gap-6 mx-auto w-fit">
                    <div class="grid grid-cols-3 gap-3 mt-10">
                        <button type="btn" class="flex justify-center w-full px-2 py-4 mx-auto font-extrabold transition duration-150 ease-in-out shadow bg-gradient-to-r from-sky-300 to-sky-400 shadow-sky-400/30 drop-shadow rounded-xl text-start text-sky-50 hover:brightness-90" v-for="n in 9">{{ n }}</button>
                        <button type="btn" class="flex justify-center w-full col-span-2 px-2 py-4 mx-auto font-extrabold transition duration-150 ease-in-out shadow bg-gradient-to-r from-sky-300 to-sky-400 shadow-sky-400/30 drop-shadow rounded-xl text-start text-sky-50 hover:brightness-90">0</button>
                        <button type="btn" class="flex justify-center w-full px-2 py-4 mx-auto font-extrabold transition duration-150 ease-in-out shadow bg-gradient-to-r from-sky-300 to-sky-400 shadow-sky-400/30 drop-shadow rounded-xl text-start text-sky-50 hover:brightness-90">Backspace</button>
                    </div>
                    <div class="grid grid-cols-2 mt-10">
                        <button type="button" @click="form.lbr_ptg = 'Kiri'"
                                class="flex justify-center w-2/3 px-2 py-4 mx-auto font-extrabold transition duration-150 ease-in-out shadow bg-gradient-to-r from-emerald-400 to-emerald-300 shadow-emerald-400/30 rounded-xl text-start text-green-50 hover:brightness-90">
                            <div class="flex flex-col items-center justify-center h-full gap-4 px-6">
                                <div class="flex gap-2">
                                    <span class="p-2 rounded-full bg-emerald-50"></span>
                                </div>
                                <span class="font-extrabold uppercase">Kiri</span>
                            </div>
                        </button>
                        <button type="button" @click="form.lbr_ptg = 'Kanan'"
                                class="flex justify-center w-2/3 px-2 py-4 mx-auto font-extrabold transition duration-150 ease-in-out shadow bg-gradient-to-r from-emerald-300 to-emerald-400 shadow-emerald-400/30 rounded-xl text-start text-green-50 hover:brightness-90">
                            <div class="flex flex-col items-center justify-center h-full gap-4 px-6">
                                <div class="flex gap-2">
                                    <span class="p-2 rounded-full bg-emerald-50"></span>
                                    <span class="p-2 rounded-full bg-emerald-50"></span>
                                </div>
                                <span class="font-extrabold uppercase">Kanan</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex justify-center gap-6 mx-auto w-fit">
                    <button type="button" @click="form.jml_rim = '1'"
                            class="flex justify-center px-4 py-4 mx-auto mt-8 shadow-md w-fit bg-gradient-to-r from-violet-400 to-violet-500 rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-violet-500/20 text-lg font-bold text-violet-50">
                        Clear
                    </button>
                    <button type="button" @click="showModal = !showModal" class="flex justify-center px-4 py-4 mx-auto mt-8 shadow-md w-fit bg-gradient-to-r from-green-400 to-green-500 rounded-xl text-start hover:brightness-90 drop-shadow-md shadow-green-500/20">
                        <div class="text-lg font-bold text-yellow-50">Generate</div>
                    </button>
                </div>
            </form>
        </div>
    </ContentLayout>
</template>
