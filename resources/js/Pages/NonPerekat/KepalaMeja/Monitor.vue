<script setup>
import ContentLayout from '@/Layouts/ContentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link,useForm } from '@inertiajs/vue3';

const form = useForm({
    team: '',
    po  : '',
    obc : '',
    seri: '',
});

</script>

<template>
    <ContentLayout>
        <div class="py-8">
            <!-- Detail Produk -->
            <!-- Team -->
            <div class="w-fit mx-auto mt-2">
                <InputLabel for="team" value="Team" class="text-4xl font-extrabold uppercase text-center"/>

                <TextInput
                    id="team"
                    ref="team"
                    v-model="form.team"
                    type="text"
                    class="mt-2 block w-full px-4 py-2 text-2xl text-center bg-slate-300"
                    autocomplete="team"
                    value="Pita Cukai NP 1"
                    disabled
                />
            </div>
            <div class="mt-10 mx-auto w-fit flex flex-row gap-8 justify-between">
                <!-- PO -->
                <div class="w-fit">
                    <InputLabel for="po" value="PO" class="text-2xl font-extrabold uppercase text-center"/>

                    <TextInput
                        id="po"
                        ref="po"
                        v-model="form.po"
                        type="number"
                        class="mt-2 block w-full px-0 py-2 text-2xl text-center bg-slate-300"
                        autocomplete="po"
                        value="3000160399"
                        disabled
                    />
                </div>

                <!-- OBC -->
                <div class="w-fit">
                    <InputLabel for="obc" value="OBC" class="text-2xl font-extrabold uppercase text-center"/>

                    <TextInput
                        id="obc"
                        ref="obc"
                        v-model="form.obc"
                        type="text"
                        class="mt-2 block w-full px-0 py-2 text-2xl text-center bg-slate-300"
                        autocomplete="obc"
                        value="PST 030001"
                        disabled
                    />
                </div>

                <!-- Seri -->
                <div class="w-fit">
                    <InputLabel for="seri" value="Seri" class="text-2xl font-extrabold uppercase text-center"/>

                    <TextInput
                        id="seri"
                        ref="seri"
                        v-model="form.seri"
                        type="number"
                        class="mt-2 block w-full px-0 py-2 text-2xl text-center bg-slate-300"
                        autocomplete="seri"
                        value="1"
                        disabled
                    />
                </div>
            </div>

            <!-- Loading State -->
            <div class="mt-6 mx-auto w-fit">
                <div class="flex justify-between w-full gap-12">
                    <!-- Row Lembar Kiri -->
                    <div class="flex flex-col gap-4 w-fit justify-center">
                        <!-- Header Lembar Kiri -->
                        <h3 class="text-3xl text-center font-medium text-slate-700">
                            Lembar Kiri
                        </h3>

                        <!-- State Kiri -->
                        <div class="grid grid-cols-5 gap-2">
                            <!-- 1 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">1</span>
                                </div>
                            </div>

                            <!-- 2 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">2</span>
                                </div>
                            </div>

                            <!-- 3 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">3</span>
                                </div>
                            </div>

                            <!-- 4 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">4</span>
                                </div>
                            </div>

                            <!-- 5 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">5</span>
                                </div>
                            </div>
                            <!-- 6 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">6</span>
                                </div>
                            </div>

                            <!-- 7 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">7</span>
                                </div>
                            </div>

                            <!-- 8 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">8</span>
                                </div>
                            </div>

                            <!-- 9 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">9</span>
                                </div>
                            </div>

                            <!-- 10 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">10</span>
                                </div>
                            </div>
                            <!-- 11 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">11</span>
                                </div>
                            </div>

                            <!-- 12 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">12</span>
                                </div>
                            </div>

                            <!-- 13 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">13</span>
                                </div>
                            </div>

                            <!-- 14 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">14</span>
                                </div>
                            </div>

                            <!-- 15 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">15</span>
                                </div>
                            </div>
                            <!-- 16 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">16</span>
                                </div>
                            </div>

                            <!-- 17 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">17</span>
                                </div>
                            </div>

                            <!-- 18 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">18</span>
                                </div>
                            </div>

                            <!-- 19 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">19</span>
                                </div>
                            </div>

                            <!-- 20 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">20</span>
                                </div>
                            </div>
                            <!-- 21 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">21</span>
                                </div>
                            </div>

                            <!-- 23 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">22</span>
                                </div>
                            </div>

                            <!-- 24 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">23</span>
                                </div>
                            </div>

                            <!-- 25 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">24</span>
                                </div>
                            </div>

                            <!-- 26 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">25</span>
                                </div>
                            </div>

                            <!-- 27 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">26</span>
                                </div>
                            </div>

                            <!-- 28 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">27</span>
                                </div>
                            </div>

                            <!-- 29 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">28</span>
                                </div>
                            </div>

                            <!-- 30 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">29</span>
                                </div>
                            </div>
                            <!-- 31 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">30</span>
                                </div>
                            </div>

                            <!-- 32 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">31</span>
                                </div>
                            </div>

                            <!-- 33 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">32</span>
                                </div>
                            </div>

                            <!-- 34 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">33</span>
                                </div>
                            </div>

                            <!-- 35 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">34</span>
                                </div>
                            </div>

                            <!-- 36 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">35</span>
                                </div>
                            </div>

                            <!-- 37 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">36</span>
                                </div>
                            </div>

                            <!-- 38 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">37</span>
                                </div>
                            </div>

                            <!-- 39 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">38</span>
                                </div>
                            </div>

                            <!-- 36 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">39</span>
                                </div>
                            </div>

                            <!-- 36 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">40</span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Row Lembar Kanan -->
                    <div class="flex flex-col gap-4 w-fit justify-center">
                        <!-- Header Lembar Kanan -->
                        <h3 class="text-3xl text-center font-medium text-slate-700">
                            Lembar Kanan
                        </h3>

                        <!-- State Kiri -->
                        <div class="grid grid-cols-5 gap-2">
                            <!-- 1 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">1</span>
                                </div>
                            </div>

                            <!-- 2 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">2</span>
                                </div>
                            </div>

                            <!-- 3 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">3</span>
                                </div>
                            </div>

                            <!-- 4 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">4</span>
                                </div>
                            </div>

                            <!-- 5 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">5</span>
                                </div>
                            </div>
                            <!-- 6 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">6</span>
                                </div>
                            </div>

                            <!-- 7 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">7</span>
                                </div>
                            </div>

                            <!-- 8 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">8</span>
                                </div>
                            </div>

                            <!-- 9 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">9</span>
                                </div>
                            </div>

                            <!-- 10 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">10</span>
                                </div>
                            </div>
                            <!-- 11 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">11</span>
                                </div>
                            </div>

                            <!-- 12 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">12</span>
                                </div>
                            </div>

                            <!-- 13 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">13</span>
                                </div>
                            </div>

                            <!-- 14 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">14</span>
                                </div>
                            </div>

                            <!-- 15 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">15</span>
                                </div>
                            </div>
                            <!-- 16 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">16</span>
                                </div>
                            </div>

                            <!-- 17 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">17</span>
                                </div>
                            </div>

                            <!-- 18 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">18</span>
                                </div>
                            </div>

                            <!-- 19 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">19</span>
                                </div>
                            </div>

                            <!-- 20 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">20</span>
                                </div>
                            </div>
                            <!-- 21 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">21</span>
                                </div>
                            </div>

                            <!-- 23 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">22</span>
                                </div>
                            </div>

                            <!-- 24 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">23</span>
                                </div>
                            </div>

                            <!-- 25 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">24</span>
                                </div>
                            </div>

                            <!-- 26 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">25</span>
                                </div>
                            </div>

                            <!-- 27 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">26</span>
                                </div>
                            </div>

                            <!-- 28 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">27</span>
                                </div>
                            </div>

                            <!-- 29 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">28</span>
                                </div>
                            </div>

                            <!-- 30 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">29</span>
                                </div>
                            </div>
                            <!-- 31 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">30</span>
                                </div>
                            </div>

                            <!-- 32 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">31</span>
                                </div>
                            </div>

                            <!-- 33 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">32</span>
                                </div>
                            </div>

                            <!-- 34 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">33</span>
                                </div>
                            </div>

                            <!-- 35 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">34</span>
                                </div>
                            </div>

                            <!-- 36 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">35</span>
                                </div>
                            </div>

                            <!-- 37 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">36</span>
                                </div>
                            </div>

                            <!-- 38 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">37</span>
                                </div>
                            </div>

                            <!-- 39 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">38</span>
                                </div>
                            </div>

                            <!-- 36 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">39</span>
                                </div>
                            </div>

                            <!-- 36 -->
                            <div class="rounded-md bg-gradient-to-br from-slate-200 to-slate-300 px-2 py-1 drop-shadow shadow">
                                <div class="flex flex-col justify-center w-[4ch]">
                                    <span class="text-xs font-extrabold leading-5 text-center">-</span>
                                    <span class="text-xs font-extrabold leading-5 text-center text-indigo-700">40</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ContentLayout>
</template>
