import { initializeTheme } from "@/composables/useAppearance";
import AppLayout from "@/layouts/AppLayout.vue";
import AuthLayout from "@/layouts/AuthLayout.vue";
import SettingsLayout from "@/layouts/settings/Layout.vue";
import { initializeFlashToast } from "@/lib/flashToast";
import { createInertiaApp } from "@inertiajs/vue3";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === "Welcome":
            case name === "auth/Login":
            case name === "Pos":
                return null;
            case name.startsWith("auth/"):
                return AuthLayout;
            case name.startsWith("settings/"):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: "#B44422",
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
