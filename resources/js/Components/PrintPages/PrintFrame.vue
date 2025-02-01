<template>
  <iframe ref="printFrame" style="display: none"></iframe>
</template>

<script setup>
import { ref } from 'vue';

const printFrame = ref(null);

const print = (content) => {
  const iframe = printFrame.value;
  const doc = iframe.contentWindow.document;

  doc.open();
  doc.write(`
    <style>
      @media print {
        @page {
          margin-left: 3rem;
          margin-right: 3rem;
          margin-top: 0rem;
        }
        body { margin: 0; }
        header, footer { display: none !important; }
        * {
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }
      }
    </style>
    ${content}`);
  doc.close();
  iframe.contentWindow.focus();

  setTimeout(() => {
    iframe.contentWindow.print();
  }, 1000);
};

defineExpose({ print });
</script>
