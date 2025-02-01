<template>
    <!-- Page title -->
    <Head title="Cetak Label" />

    <!-- Modal for reprinting labels -->
    <PrintUlangModal
        :show="printUlangModal"
        :product-data="props.product"
        :color-obc="colorObc"
        :team="form.team"
        @close="printUlangModal = false"
        @success="handlePrintSuccess"
        @error="showNotification"
        @print="printWithoutDialog"
    />

    <!-- Add this line after PrintUlangModal -->
    <PrintLabelKosongModal
        :show="printManualModal"
        :obc="form.obc"
        :color-obc="colorObc"
        :team="form.team"
        @close="printManualModal = false"
        @success="showNotification('Label berhasil dicetak', 'success')"
        @error="showNotification"
        @print="printWithoutDialog"
    />

    <!-- Main layout -->
    <AuthenticatedLayout>
        <div class="w-full max-w-5xl bg-white dark:bg-gray-800 rounded-lg py-4 shadow-md px-6 mx-auto mt-8 flex flex-col gap-3 mb-8">
        <!-- Title -->
            <h1 class="text-3xl font-bold text-[#4B5563] dark:text-gray-200 my-auto text-center mb-4 pb-4 border-b border-sky-600">
                <span class="text-red-600 dark:text-red-400" v-if="form.seri == 3">
                    {{ form.obc }}
                </span>
                <span class="text-blue-600 dark:text-blue-400" v-else>
                    {{ form.obc }}
                </span>
                -
                <span class="text-blue-600 dark:text-blue-400">
                    {{ form.noPlat }}
                </span>
            </h1>
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Team selection -->
                <div>
                    <InputLabel for="team" value="Team" class="text-xl font-bold mb-3 dark:text-gray-300" />
                    <select
                        id="team"
                        ref="team"
                        v-model="form.team"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300"
                    >
                        <option v-for="team in props.listTeam" :key="team.id" :value="team.id">
                            {{ team.workstation }}
                        </option>
                    </select>
                </div>

                <!-- Order details grid -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- PO Number -->
                    <div>
                        <InputLabel for="po" value="Nomor PO" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <TextInput
                            id="po"
                            ref="po"
                            v-model="form.po"
                            type="number"
                            class="w-full bg-gray-50 dark:bg-gray-700 text-center dark:text-gray-300"
                            disabled
                        />
                    </div>

                    <!-- OBC Number -->
                    <div>
                        <InputLabel for="obc" value="Nomor OBC" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <TextInput
                            id="obc"
                            ref="obc"
                            v-model="form.obc"
                            type="text"
                            class="w-full bg-gray-50 dark:bg-gray-700 text-center dark:text-gray-300"
                            disabled
                        />
                    </div>

                    <!-- Series -->
                    <div>
                        <InputLabel for="seri" value="Seri" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <TextInput
                            id="seri"
                            ref="seri"
                            v-model="form.seri"
                            type="number"
                            class="w-full bg-gray-50 dark:bg-gray-700 text-center dark:text-gray-300"
                            disabled
                        />
                    </div>
                </div>

                <!-- Additional details grid -->
                <div class="grid grid-cols-3 gap-6">
                    <!-- Rim Number -->
                    <div>
                        <InputLabel for="no_rim" value="Nomor Rim" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <template v-if="form.no_rim !== 999">
                            <TextInput
                                id="no_rim"
                                ref="no_rim"
                                v-model="form.no_rim"
                                type="number"
                                class="w-full bg-gray-50 dark:bg-gray-700 text-center font-bold dark:text-gray-300"
                                disabled
                            />
                        </template>
                        <template v-else>
                            <TextInput
                                type="text"
                                value="Inschiet"
                                class="w-full bg-gray-50 dark:bg-gray-700 text-center font-bold dark:text-gray-300"
                                disabled
                            />
                        </template>
                    </div>

                    <!-- Cut Sheet -->
                    <div>
                        <InputLabel for="lbr_ptg" value="Lembar Potong" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <TextInput
                            id="lbr_ptg"
                            ref="lbr_ptg"
                            v-model="form.lbr_ptg"
                            type="text"
                            class="w-full bg-gray-50 dark:bg-gray-700 text-center font-bold dark:text-gray-300"
                            disabled
                        />
                    </div>

                    <!-- Plate Code -->
                    <div>
                        <InputLabel for="noPlat" value="Kode Plat" class="text-xl font-bold mb-3 dark:text-gray-300" />
                        <TextInput
                            id="noPlat"
                            ref="noPlat"
                            v-model="form.noPlat"
                            type="number"
                            class="w-full bg-gray-50 dark:bg-gray-700 text-center font-bold dark:text-gray-300"
                            disabled
                        />
                    </div>
                </div>

                <!-- Operator ID -->
                <div>
                    <InputLabel for="periksa1" value="Silahkan Scan NP mu" class="text-xl font-bold mb-3 dark:text-gray-300" />
                    <TextInput
                        id="periksa1"
                        type="text"
                        v-model="form.periksa1"
                        class="w-full uppercase dark:bg-gray-700 dark:text-gray-300"
                        required
                        ref="periksa1Input"
                        autofocus
                    />
                    <InputError class="mt-2" />
                </div>

                <!-- Action buttons -->
                <div class="flex justify-center gap-4">
                    <!-- clear -->
                    <button
                        type="button"
                        @click="form.periksa1 = null"
                        class="px-6 py-3 text-white bg-violet-600 dark:bg-violet-700 hover:bg-violet-700 dark:hover:bg-violet-800 rounded-lg transition-colors"
                    >
                        Clear
                    </button>

                    <!-- generate label -->
                    <button
                        type="submit"
                        :disabled="loading"
                        :class="[
                            'px-6 py-3 text-white bg-green-600 dark:bg-green-700 hover:bg-green-700 dark:hover:bg-green-800 rounded-lg transition-colors',
                            loading ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                    >
                        {{ loading ? 'Memproses...' : 'Generate' }}
                    </button>

                    <!-- Add Print Ulang button here -->
                    <button
                        type="button"
                        @click="printUlangModal = true"
                        class="px-6 py-3 text-blue-600 bg-inherit dark:bg-inherit border border-blue-600 dark:border-blue-700 hover:bg-blue-50 dark:hover:bg-blue-800 rounded-lg transition-colors"
                    >
                        Print Ulang
                    </button>

                    <!-- print label manual -->
                    <button
                        type="button"
                        @click="printManualModal = true"
                        class="px-6 py-3 text-green-600 bg-inherit dark:bg-inherit border border-green-600 dark:border-green-700 hover:bg-green-50 dark:hover:bg-green-800 rounded-lg transition-colors"
                    >
                        Print Manual
                    </button>
                </div>

                <button
                    type="button"
                    @click="confirmFinishOrder"
                    class="w-full px-6 py-3 text-white bg-cyan-600 dark:bg-cyan-700 hover:bg-cyan-700 dark:hover:bg-cyan-800 rounded-lg transition-colors"
                >
                    Selesaikan Order
                </button>
            </form>

            <!-- Navigation -->
            <div class="flex justify-center gap-4 mt-4">
                <a
                    href="#"
                    onclick="history.back()"
                    class="inline-flex items-center px-6 py-3 text-white bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 rounded-lg transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </a>
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center px-6 py-3 text-white bg-blue-600 dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 rounded-lg transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Home
                </Link>
            </div>
        </div>
        <TableVerifikasiPegawai :team="form.team" :date="form.date"/>
    </AuthenticatedLayout>
    <iframe ref="printFrame" style="display: none"></iframe>
</template>

<script setup>
import { reactive, ref, onMounted, nextTick, onBeforeUnmount } from "vue";
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import TableVerifikasiPegawai from "@/Components/TableVerifikasiPegawai.vue";
import { singleLabel, batchSingleLabel } from "@/Components/PrintPages/index";
import { Link, useForm, router, Head } from "@inertiajs/vue3";
import axios from "axios";
import Swal from 'sweetalert2';
import PrintUlangModal from './Modals/PrintUlangModal.vue';
import PrintLabelKosongModal from './Modals/PrintLabelKosongModal.vue';

// Props definition
const props = defineProps({
    product: Object,        // Product details
    listTeam: Object,      // List of available teams
    crntTeam: Number,      // Current team ID
    noRim: Number,         // Rim number
    potongan: String,      // Cut sheet info
    date: String,          // Current date
});

// Reactive refs for modal states
const showModal = ref(false);
const printUlangModal = ref(false);
const dataPrintUlang = ref();

// Add loading state ref
const loading = ref(false);

// Form for main data entry
const form = useForm({
    id: props.product.id,
    po: props.product.no_po,
    obc: props.product.no_obc,
    team: props.crntTeam,
    seri: props.product.no_obc.substr(4, 1) > 3 ? 1 : props.product.no_obc.substr(4, 1),
    jml_rim: 1,
    lbr_ptg: props.potongan,
    no_rim: props.noRim,
    periksa1: "",
    date: props.date,
    noPlat: "",
});

// Color coding for OBC series
const colorObc = form.seri == 3 ? "#b91c1c" : "#1d4ed8";

// Functions for handling rim data
const dataRimKanan = async () => {
    formPrintUlang.dataRim = "Kanan";
    getDataRim();
};

const dataRimKiri = async () => {
    formPrintUlang.dataRim = "Kiri";
    getDataRim();
};

// Fetch rim data from API
const getDataRim = () => {
    axios
        .post("/api/order-besar/cetak-label/edit", formPrintUlang)
        .then((res) => {
            dataPrintUlang.value = res.data;
        });
};

// Handle rim selection
const pilihRim = (noRim, np) => {
    formPrintUlang.noRim = noRim;
    formPrintUlang.npPetugas = np;
};

// Print frame reference
const printFrame = ref(null);

// Add memory pooling for frequently used objects
const objectPool = {
  pool: new Map(),

  acquire(key) {
    if (!this.pool.has(key)) {
      this.pool.set(key, new Map());
    }
    return this.pool.get(key);
  },

  release(key) {
    if (this.pool.has(key)) {
      this.pool.get(key).clear();
    }
  },

  clear() {
    this.pool.clear();
  }
};

// Improved memory manager with string interning
const memoryManager = {
  clearDOMRefs: () => {
    // Clear DOM references
    if (printFrame.value) {
      printFrame.value.srcdoc = '';
      printFrame.value = null;
    }
    if (periksa1Input.value) {
      periksa1Input.value = null;
    }
  },

  clearReactiveData: () => {
    // Clear reactive data
    dataPrintUlang.value = null;
    loading.value = false;
    printUlangModal.value = false;
    printManualModal.value = false;
  },

  resetForms: () => {
    // Reset form states
    form.reset();
    Object.assign(formPrintUlang, {
      dataRim: "Kiri",
      noRim: "",
      npPetugas: "",
      po: props.product.no_po,
      obc: props.product.no_obc,
      team: props.crntTeam,
    });
  },

  stringCache: new Map(),

  internString(str) {
    if (!this.stringCache.has(str)) {
      this.stringCache.set(str, str);
    }
    return this.stringCache.get(str);
  },

  clearCache() {
    this.stringCache.clear();
    objectPool.clear();
  },

  optimizeDOM() {
    // Remove unnecessary DOM elements
    const unusedModals = document.querySelectorAll('.modal:not(:visible)');
    unusedModals.forEach(modal => {
      modal.innerHTML = '';
    });

    // Clear print frame
    if (printFrame.value) {
      const doc = printFrame.value.contentWindow.document;
      doc.open();
      doc.write('');
      doc.close();
    }
  }
};

// Optimize print function to reduce DOM operations
const printWithoutDialog = (content) => {
  const iframe = printFrame.value;
  if (!iframe) return;

  // Reuse document reference
  const doc = iframe.contentWindow.document;

  // Use DocumentFragment for better performance
  const fragment = doc.createDocumentFragment();
  const container = doc.createElement('div');

  // Add styles once
  const style = doc.createElement('style');
  style.textContent = `
    @media print {
      @page { margin-left: 3rem; margin-right:3rem; margin-top:1rem; }
      * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  `;

  fragment.appendChild(style);
  container.innerHTML = content;
  fragment.appendChild(container);

  // Clear existing content
  doc.open();
  doc.write('');
  doc.close();

  // Add new content
  doc.body.appendChild(fragment);

  const timeoutId = setTimeout(() => {
    iframe.contentWindow.focus();
    iframe.contentWindow.print();

    // Clean up after printing
    requestAnimationFrame(() => {
      doc.body.innerHTML = '';
    });
  }, 100);

  window._timeouts = window._timeouts || [];
  window._timeouts.push(timeoutId);
};

// Optimize data fetching to reduce object creation
const fetchUpdatedData = async () => {
  const controller = new AbortController();
  const cache = objectPool.acquire('fetchData');

  try {
    if (dataPrintUlang.value) {
      dataPrintUlang.value = null;
    }

    const { data } = await axios.get(
      `/api/order-besar/cetak-label/data/${form.team}/${form.id}`,
      {
        signal: controller.signal,
        headers: {
          'Cache-Control': 'no-cache',
          'Pragma': 'no-cache'
        }
      }
    );

    if (data) {
      // Reuse objects where possible
      if (cache.has(data.noRim)) {
        form.no_rim = cache.get(data.noRim);
      } else {
        cache.set(data.noRim, data.noRim);
        form.no_rim = data.noRim;
      }

      form.lbr_ptg = memoryManager.internString(data.potongan);
      dataPrintUlang.value = data.printData;
    }
  } catch (error) {
    if (!axios.isCancel(error)) {
      console.error('Error fetching updated data:', error);
      showNotification('Gagal memperbarui data', 'error');
    }
  } finally {
    controller.abort();
  }
};

// Enhanced cleanup
const cleanup = () => {
  memoryManager.clearDOMRefs();
  memoryManager.clearReactiveData();
  memoryManager.resetForms();
  memoryManager.optimizeDOM();
  memoryManager.clearCache();

  if (window._timeouts) {
    window._timeouts.forEach(timeout => clearTimeout(timeout));
    window._timeouts = [];
  }

  // Force garbage collection hint
  if (window.gc) {
    window.gc();
  }
};

// Optimize component lifecycle
onMounted(() => {
  window._timeouts = [];
  // Pre-allocate common strings
  ['Kiri', 'Kanan', 'INS'].forEach(str =>
    memoryManager.internString(str)
  );
});

onBeforeUnmount(() => {
  cleanup();
});

// Add periodic cleanup for long-running sessions
let cleanupInterval;
onMounted(() => {
  cleanupInterval = setInterval(() => {
    memoryManager.optimizeDOM();
  }, 60000); // Run every minute
});

onBeforeUnmount(() => {
  if (cleanupInterval) {
    clearInterval(cleanupInterval);
  }
});

// Optimize form submission
const submit = async (e) => {
  e.preventDefault();
  if (loading.value) return;

  loading.value = true;
  const controller = new AbortController();

  try {
    // Clear previous data
    memoryManager.clearReactiveData();

    const { data } = await axios.post("/api/order-besar/cetak-label", form, {
      signal: controller.signal,
      headers: {
        'Cache-Control': 'no-cache',
        'Pragma': 'no-cache'
      }
    });

    if (data.status === 'error') {
      showNotification(data.message, 'error');
      return;
    }

    const printLabel = singleLabel(
      form.obc,
      form.no_rim !== 999 ? form.no_rim : "INS",
      colorObc,
      form.lbr_ptg == "Kiri" ? "(*)" : "(**)",
      form.periksa1,
      undefined,
      500,
    );

    await printWithoutDialog(printLabel);

    if (data.poStatus !== 2) {
      form.periksa1 = null;

      if (data.data) {
        form.no_rim = data.data.no_rim;
        form.lbr_ptg = data.data.potongan;
      }

      await fetchUpdatedData();
      showNotification('Label berhasil dicetak', 'success');

      await nextTick(() => {
        periksa1Input.value?.focus();
      });
    } else {
      router.get("/order-besar/po-siap-verif", {}, { preserveState: true });
    }
  } catch (error) {
    if (!axios.isCancel(error)) {
      console.error('Error:', error);
      showNotification('Gagal mencetak label', 'error');
    }
  } finally {
    loading.value = false;
    controller.abort();
  }
};

// Add notification system
const showNotification = (message, type = 'info') => {
    // You can use a toast library like vue-toastification
    // or SweetAlert2 for notifications
    Swal.fire({
        title: message,
        icon: type,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
};

// Handle order completion confirmation with SweetAlert2
const confirmFinishOrder = async () => {
    try {
        const result = await Swal.fire({
            title: 'Selesaikan Order',
            text: 'Apakah Anda yakin ingin menyelesaikan order ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesaikan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#0891b2',
            cancelButtonColor: '#6b7280',
        });

        if (result.isConfirmed) {
            await axios.put(`/api/production-order-finish/${form.po}`);
            router.get("/order-besar/po-siap-verif", {}, { preserveState: true });
            showNotification('Order berhasil diselesaikan', 'success');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Gagal menyelesaikan order', 'error');
    }
};

// Fetch plate number from external API
const fetchNoPlat = async () => {
    try {
        const response = await axios.get(
            `http://10.18.30.233:8300/api/nomor-plat-pcht/${form.obc}`
        );
        form.noPlat = response.data;
    } catch (error) {
        console.error("Error fetching noPlat:", error);
    }
};

// Initial plate number fetch
fetchNoPlat();

// Add a new method to handle order completion status
const checkOrderCompletion = (message) => {
    if (message === 'Order sudah selesai') {
        Swal.fire({
            title: 'Order Selesai',
            text: 'Semua label telah selesai diproses',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#0891b2'
        }).then((result) => {
            if (result.isConfirmed) {
                router.get("/order-besar/po-siap-verif", {}, { preserveState: true });
            }
        });
        return true;
    }
    return false;
};

// Add ref for input focus
const periksa1Input = ref(null);

// Add this to the script setup section
const printManualModal = ref(false);

// Add success handler
const handlePrintSuccess = async () => {
    await fetchUpdatedData();
    showNotification('Label berhasil dicetak', 'success');
};
</script>
