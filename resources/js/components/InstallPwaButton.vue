<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const deferredPrompt = ref(null);
const showInstallBanner = ref(false);

const handleBeforeInstallPrompt = (e) => {
  // Prevent the mini-infobar from appearing on mobile
  e.preventDefault();
  // Stash the event so it can be triggered later
  deferredPrompt.value = e;
  // Show your custom UI button/banner
  showInstallBanner.value = true;
};

const installApp = async () => {
  if (!deferredPrompt.value) return;

  // Show the native browser install prompt
  deferredPrompt.value.prompt();

  // Wait for the user to respond to the prompt
  const { outcome } = await deferredPrompt.value.userChoice;
  console.log(`User response to the install prompt: ${outcome}`);

  // Clear the saved prompt since it can only be used once
  deferredPrompt.value = null;
  showInstallBanner.value = false;
};

const handleAppInstalled = () => {
  showInstallBanner.value = false;
  deferredPrompt.value = null;
};

onMounted(() => {
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
  window.addEventListener('appinstalled', handleAppInstalled);
});

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
  window.removeEventListener('appinstalled', handleAppInstalled);
});
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <div
      v-if="showInstallBanner"
      class="w-full border-b border-blue-700/20 bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-lg dark:border-blue-500/30 dark:from-slate-800 dark:to-slate-900 dark:text-slate-100 dark:shadow-black/40"
      role="banner"
      aria-label="Install app banner"
    >
      <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-6 lg:px-8">
        <!-- Icon + message -->
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/25 dark:bg-blue-500/15 dark:ring-blue-400/30">
            <svg class="h-5 w-5 text-white dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
            </svg>
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold leading-tight">
              Install the BazarioTech app
            </p>
            <p class="text-xs text-blue-100 leading-tight dark:text-slate-400">
              Faster shopping, offline browsing &amp; exclusive deals.
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2 sm:flex-shrink-0">
          <button
            @click="installApp"
            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-blue-600 active:scale-[0.98] dark:bg-blue-500 dark:text-white dark:hover:bg-blue-400 dark:focus-visible:ring-blue-400 dark:focus-visible:ring-offset-slate-900"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16" />
            </svg>
            Install
          </button>

          <button
            @click="showInstallBanner = false"
            class="inline-flex items-center justify-center rounded-lg p-2 text-blue-100 transition hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-slate-100 dark:focus-visible:ring-blue-400"
            aria-label="Dismiss install banner"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>