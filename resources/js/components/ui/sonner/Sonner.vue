
<script lang="ts" setup>
import type { ToasterProps } from "vue-sonner"
import {
    CheckIcon,
    InfoIcon,
    Loader2Icon,
    OctagonXIcon,
    TriangleAlertIcon,
    XIcon,
} from "@lucide/vue"
import { Toaster as Sonner } from "vue-sonner"
import { cn } from "@/lib/utils"

import "vue-sonner/style.css"

const props = defineProps<ToasterProps>()
</script>

<template>
    <Sonner
        :class="cn('toaster group', props.class)"
        :style="{
            '--normal-bg': '#ffffff',
            '--normal-text': '#172033',
            '--normal-border': '#f1f5f9',
            '--border-radius': '20px',
            '--width': '600px',
        }"
        v-bind="props"
    >
        <template #success-icon>
            <div class="success-icon">
                <div class="success-icon-inner">
                    <CheckIcon class="size-5 stroke-[3]" />
                </div>
            </div>
        </template>

        <template #info-icon>
            <div class="notification-icon info-icon">
                <InfoIcon class="size-7" />
            </div>
        </template>

        <template #warning-icon>
            <div class="notification-icon warning-icon">
                <TriangleAlertIcon class="size-7" />
            </div>
        </template>

        <template #error-icon>
            <div class="notification-icon error-icon">
                <OctagonXIcon class="size-7" />
            </div>
        </template>

        <template #loading-icon>
            <div class="notification-icon loading-icon">
                <Loader2Icon class="size-7 animate-spin" />
            </div>
        </template>

        <template #close-icon>
            <XIcon class="size-6" />
        </template>
    </Sonner>
</template>

<style>

/* =========================================
   MAIN TOAST CARD
========================================= */

.toaster [data-sonner-toast] {
    display: grid !important;
    grid-template-columns: 54px minmax(0, 1fr) !important;
    align-items: start !important;
    column-gap: 18px !important;

    width: min(480px, calc(100vw - 32px)) !important;
    min-height: 0 !important;
    height: auto !important;

    padding: 22px 46px 22px 22px !important;

    background: #ffffff !important;
    color: #172033 !important;
    border: 1px solid #f1f5f9 !important;
    border-radius: 18px !important;

    box-shadow:
        0 12px 24px -8px rgb(15 23 42 / 12%),
        0 4px 8px -4px rgb(15 23 42 / 8%) !important;
}

/* =========================================
   ICON POSITIONING
========================================= */

.toaster [data-sonner-toast] [data-icon] {
    grid-column: 1 !important;
    grid-row: 1 !important;

    width: 54px !important;
    height: 54px !important;
    min-width: 54px !important;

    margin: 0 !important;
    padding: 0 !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

/* =========================================
   CONTENT POSITIONING
========================================= */

.toaster [data-sonner-toast] [data-content] {
    grid-column: 2 !important;
    grid-row: 1 !important;

    min-width: 0 !important;
    width: 100% !important;

    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 5px !important;

    margin: 0 !important;
    padding: 0 !important;
    text-align: left !important;
}

/* =========================================
   NOTIFICATION TYPE HEADINGS
========================================= */

/* Success */
.toaster [data-sonner-toast][data-type="success"] [data-title]::before {
    content: "Success";
}

/* Error */
.toaster [data-sonner-toast][data-type="error"] [data-title]::before {
    content: "Error";
}

/* Warning */
.toaster [data-sonner-toast][data-type="warning"] [data-title]::before {
    content: "Warning";
}

/* Information */
.toaster [data-sonner-toast][data-type="info"] [data-title]::before {
    content: "Information";
}

/* Loading */
.toaster [data-sonner-toast][data-type="loading"] [data-title]::before {
    content: "Processing";
}

/* Type heading */
.toaster [data-sonner-toast] [data-title]::before {
    display: block;
    margin-bottom: 4px;

    color: #172033;
    font-size: 17px;
    font-weight: 650;
    line-height: 1.4;
    letter-spacing: -0.25px;
}

/* Actual notification message */
.toaster [data-sonner-toast] [data-title] {
    margin: 0 !important;
    padding: 0 !important;

    color: #718096 !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    line-height: 1.55 !important;
    letter-spacing: 0 !important;
}

/* Additional description, when provided */
.toaster [data-sonner-toast] [data-description] {
    margin: 2px 0 0 !important;
    padding: 0 !important;

    color: #718096 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    line-height: 1.5 !important;
}

/* =========================================
   SUCCESS ICON
========================================= */

.toaster .success-icon {
    width: 54px !important;
    height: 54px !important;
    min-width: 54px !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    border-radius: 50% !important;
    background: #dcfce7 !important;
}

.toaster .success-icon-inner {
    width: 29px !important;
    height: 29px !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    border-radius: 50% !important;
    background: #22c55e !important;
    color: white !important;
}

/* =========================================
   OTHER NOTIFICATION ICONS
========================================= */

.toaster .notification-icon {
    width: 54px !important;
    height: 54px !important;
    min-width: 54px !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    border-radius: 50% !important;
}

.toaster .info-icon {
    background: #dbeafe !important;
    color: #2563eb !important;
}

.toaster .warning-icon {
    background: #fef3c7 !important;
    color: #d97706 !important;
}

.toaster .error-icon {
    background: #fee2e2 !important;
    color: #dc2626 !important;
}

.toaster .loading-icon {
    background: #f1f5f9 !important;
    color: #64748b !important;
}

/* =========================================
   ACTION BUTTONS
========================================= */

.toaster [data-sonner-toast] [data-actions] {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    justify-content: flex-start !important;
    gap: 24px !important;

    margin-top: 13px !important;
    padding: 0 !important;
}

.toaster [data-sonner-toast] [data-button],
.toaster [data-sonner-toast] [data-cancel] {
    height: auto !important;
    min-height: 0 !important;

    padding: 0 !important;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;

    font-size: 14px !important;
    font-weight: 550 !important;
    line-height: 1.5 !important;
    cursor: pointer;
}

.toaster [data-sonner-toast] [data-cancel] {
    order: 1;
    color: #64748b !important;
}

.toaster [data-sonner-toast] [data-button] {
    order: 2;
    color: #2563eb !important;
}

/* =========================================
   CLOSE BUTTON
========================================= */

.toaster [data-sonner-toast] [data-close-button] {
    position: absolute !important;
    top: 17px !important;
    right: 17px !important;
    left: auto !important;

    width: 26px !important;
    height: 26px !important;

    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    padding: 0 !important;
    border: none !important;
    background: transparent !important;
    color: #64748b !important;
    box-shadow: none !important;
}


/* =========================================
   MOBILE RESPONSIVENESS
========================================= */

@media (max-width: 640px) {
    .toaster [data-sonner-toast] {
        grid-template-columns: 44px minmax(0, 1fr) !important;
        column-gap: 13px !important;

        width: calc(100vw - 24px) !important;
        padding: 17px 40px 17px 17px !important;
        border-radius: 16px !important;
    }

    .toaster [data-sonner-toast] [data-icon],
    .toaster .success-icon,
    .toaster .notification-icon {
        width: 44px !important;
        height: 44px !important;
        min-width: 44px !important;
    }

    .toaster .success-icon-inner {
        width: 25px !important;
        height: 25px !important;
    }

    .toaster [data-sonner-toast] [data-title]::before {
        font-size: 15px;
    }

    .toaster [data-sonner-toast] [data-title] {
        font-size: 13px !important;
    }

    .toaster [data-sonner-toast] [data-description] {
        font-size: 12px !important;
    }

    .toaster [data-sonner-toast] [data-button],
    .toaster [data-sonner-toast] [data-cancel] {
        font-size: 13px !important;
    }

    .toaster [data-sonner-toast] [data-actions] {
        margin-top: 11px !important;
        gap: 20px !important;
    }
}

</style>
