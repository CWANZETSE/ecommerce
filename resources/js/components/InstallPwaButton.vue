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
  <div v-if="showInstallBanner" class="pwa-install-banner">
    <p>Install our app for a better experience!</p>
    <button @click="installApp">Install App</button>
    <button @click="showInstallBanner = false">Dismiss</button>
  </div>
</template>