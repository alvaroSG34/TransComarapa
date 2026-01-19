<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CountryFlag from '@/Components/CountryFlag.vue';
import { Head } from '@inertiajs/vue3';
import { PAISES } from '@/utils/paises.js';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Demo Banderas" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h1 class="text-3xl font-bold mb-8">🏳️ Demo de Banderas flag-icons</h1>

                        <!-- Componente CountryFlag -->
                        <section class="mb-12">
                            <h2 class="text-2xl font-semibold mb-4">Componente CountryFlag</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Básico -->
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Básico:</p>
                                    <CountryFlag country="Bolivia" />
                                </div>

                                <!-- Con moneda -->
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Con moneda:</p>
                                    <CountryFlag country="Argentina" :show-currency="true" />
                                </div>

                                <!-- Solo bandera -->
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Solo bandera:</p>
                                    <CountryFlag country="Chile" :show-name="false" />
                                </div>

                                <!-- Tamaños -->
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Tamaños:</p>
                                    <div class="flex items-center gap-4">
                                        <CountryFlag country="Brasil" size="sm" :show-name="false" />
                                        <CountryFlag country="Brasil" size="md" :show-name="false" />
                                        <CountryFlag country="Brasil" size="lg" :show-name="false" />
                                        <CountryFlag country="Brasil" size="xl" :show-name="false" />
                                    </div>
                                </div>

                                <!-- Cuadrada -->
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Cuadrada (squared):</p>
                                    <CountryFlag country="México" :squared="true" />
                                </div>

                                <!-- Grande con todo -->
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">XL con moneda:</p>
                                    <CountryFlag country="Estados Unidos" :show-currency="true" size="xl" />
                                </div>
                            </div>
                        </section>

                        <!-- Clases Directas -->
                        <section class="mb-12">
                            <h2 class="text-2xl font-semibold mb-4">Clases Directas flag-icons</h2>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <!-- Rectangular -->
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center">
                                    <span class="fi fi-bo" style="width: 2em; height: 1.5em;"></span>
                                    <p class="text-xs mt-2">Rectangular</p>
                                    <code class="text-xs">fi fi-bo</code>
                                </div>

                                <!-- Cuadrada -->
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center">
                                    <span class="fi fi-bo fis" style="width: 2em; height: 2em;"></span>
                                    <p class="text-xs mt-2">Cuadrada</p>
                                    <code class="text-xs">fi fi-bo fis</code>
                                </div>

                                <!-- Con sombra -->
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center">
                                    <span class="fi fi-ar shadow-md" style="width: 2em; height: 1.5em; border-radius: 4px;"></span>
                                    <p class="text-xs mt-2">Con sombra</p>
                                    <code class="text-xs">+ shadow-md</code>
                                </div>

                                <!-- Redondeada -->
                                <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center">
                                    <span class="fi fi-cl" style="width: 2em; height: 1.5em; border-radius: 8px; overflow: hidden;"></span>
                                    <p class="text-xs mt-2">Redondeada</p>
                                    <code class="text-xs">+ rounded</code>
                                </div>
                            </div>
                        </section>

                        <!-- Todas las Banderas -->
                        <section>
                            <h2 class="text-2xl font-semibold mb-4">Todos los Países Disponibles</h2>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div 
                                    v-for="pais in PAISES" 
                                    :key="pais.iso"
                                    class="flex items-center gap-3 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                >
                                    <span 
                                        :class="`fi fi-${pais.iso.toLowerCase()}`"
                                        style="width: 1.5em; height: 1.125em; flex-shrink: 0;"
                                    ></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium truncate">{{ pais.nombre }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ pais.codigo }} • {{ pais.simbolo }} {{ pais.moneda }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Código de Ejemplo -->
                        <section class="mt-12">
                            <h2 class="text-2xl font-semibold mb-4">Código de Ejemplo</h2>
                            
                            <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
                                <pre><code>&lt;!-- Componente --&gt;
&lt;CountryFlag country="Bolivia" /&gt;
&lt;CountryFlag country="Argentina" :show-currency="true" /&gt;
&lt;CountryFlag country="Chile" :squared="true" size="lg" /&gt;

&lt;!-- Clases directas --&gt;
&lt;span class="fi fi-bo"&gt;&lt;/span&gt;
&lt;span class="fi fi-ar fis"&gt;&lt;/span&gt; &lt;!-- cuadrada --&gt;

&lt;!-- En selectores --&gt;
&lt;div class="relative"&gt;
  &lt;span class="fi fi-bo absolute left-3 top-1/2 -translate-y-1/2"&gt;&lt;/span&gt;
  &lt;select class="pl-11"&gt;...&lt;/select&gt;
&lt;/div&gt;</code></pre>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.fi {
    display: inline-block;
    vertical-align: middle;
    border-radius: 2px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}
</style>
