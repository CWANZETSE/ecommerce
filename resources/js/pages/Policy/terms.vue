<script setup>
import StorefrontLayout from '@/layouts/StorefrontLayout.vue'
defineOptions({ layout: StorefrontLayout })
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-16 sm:px-6 lg:px-8 dark:bg-navy-900 dark:text-navy-300">
    <!-- Header -->
    <header class="mb-12 border-b border-gray-200 pb-8">
      <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">
        Terms of Service
      </h1>
      <p class="mt-4 text-lg text-gray-600">
        {{ $page.props.store_name }}
      </p>
      <p class="mt-1 text-sm text-gray-500">
        Effective Date: November 20, 2026
      </p>
    </header>

    <!-- Intro -->
    <section class="mb-10">
      <p class="text-gray-700 leading-relaxed">
        Welcome to {{ $page.props.store_name }}. These Terms of Service ("Terms") govern your access to and use of the {{ $page.props.store_name }} website, mobile applications, and services (collectively, the "Services"). By accessing or using the Services, you agree to be bound by these Terms. If you do not agree to all of these Terms, you may not use the Services.
      </p>
    </section>

    <!-- Section 1 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Eligibility & Account Registration</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        To use certain features of {{ $page.props.store_name }}, you must register for an account. You represent and warrant that:
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li>You are at least 18 years of age or the age of majority in your jurisdiction.</li>
        <li>All registration information you submit is accurate, current, and complete.</li>
        <li>You will maintain the security of your password and accept all risks of unauthorized access to your account.</li>
        <li>You will promptly notify {{ $page.props.store_name }} of any unauthorized use of your account.</li>
      </ul>
    </section>

    <!-- Section 2 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Products, Pricing & Availability</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        All products displayed on {{ $page.props.store_name }} are subject to availability. We reserve the right to discontinue any product at any time.
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li><strong>Pricing:</strong> Prices are displayed in the applicable currency and are subject to change without notice. {{ $page.props.store_name }} reserves the right to correct pricing errors and to refuse or cancel orders placed at an incorrect price.</li>
        <li><strong>Taxes:</strong> Applicable sales tax, VAT, or duties will be added to your order total where required by law.</li>
        <li><strong>Product Descriptions:</strong> We strive for accuracy but do not warrant that product descriptions, images, or other content are error-free, complete, or current.</li>
      </ul>
    </section>

    <!-- Section 3 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Orders & Payment</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        By placing an order on {{ $page.props.store_name }}, you make an offer to purchase the products in your cart. We reserve the right to accept or decline your order for any reason, including product unavailability, errors in pricing, or suspected fraud.
      </p>
      <p class="text-gray-700 leading-relaxed mb-4">
        You agree to provide current, complete, and accurate payment information. By submitting payment information, you authorize {{ $page.props.store_name }} and its payment processors to charge the applicable amount.
      </p>
      <p class="text-gray-700 leading-relaxed">
        Your order is not confirmed until you receive a shipping confirmation email from {{ $page.props.store_name }}.
      </p>
    </section>

    <!-- Section 4 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Shipping, Returns & Refunds</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        Shipping timelines and costs are described in our Shipping Policy, which is incorporated into these Terms by reference.
      </p>
      <p class="text-gray-700 leading-relaxed">
        Returns and refunds are governed by our Return Policy. In general, items must be returned in their original condition within the specified return window. {{ $page.props.store_name }} reserves the right to refuse returns that do not meet our policy requirements.
      </p>
    </section>

    <!-- Section 5 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Intellectual Property</h2>
      <p class="text-gray-700 leading-relaxed">
        All content on {{ $page.props.store_name }}—including text, graphics, logos, images, product descriptions, and software—is the property of {{ $page.props.store_name }} or its licensors and is protected by copyright, trademark, and other intellectual property laws. You may not reproduce, distribute, modify, or create derivative works of any content without our express written permission.
      </p>
    </section>

    <!-- Section 6 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">6. User Conduct</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        You agree not to use the Services for any unlawful purpose or in any way that could damage, disable, or impair the Services. Prohibited activities include:
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li>Posting false, misleading, or defamatory reviews or content.</li>
        <li>Attempting to gain unauthorized access to other users' accounts or our systems.</li>
        <li>Using automated means (bots, scrapers) to access the Services without permission.</li>
        <li>Engaging in fraudulent transactions or identity theft.</li>
      </ul>
    </section>

    <!-- Section 7 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Disclaimer of Warranties</h2>
      <p class="text-gray-700 leading-relaxed">
        THE SERVICES AND ALL PRODUCTS OFFERED THROUGH {{ $page.props.store_name?.toUpperCase() }} ARE PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EITHER EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT. {{ $page.props.store_name?.toUpperCase() }} DOES NOT WARRANT THAT THE SERVICES WILL BE UNINTERRUPTED, ERROR-FREE, OR FREE OF VIRUSES OR OTHER HARMFUL COMPONENTS.
      </p>
    </section>

    <!-- Section 8 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Limitation of Liability</h2>
      <p class="text-gray-700 leading-relaxed">
        TO THE FULLEST EXTENT PERMITTED BY LAW, {{ $page.props.store_name?.toUpperCase() }} AND ITS OFFICERS, DIRECTORS, EMPLOYEES, AND AGENTS SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES ARISING FROM YOUR USE OF THE SERVICES OR ANY PRODUCTS PURCHASED THROUGH THE SERVICES, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGES. IN NO EVENT SHALL OUR TOTAL LIABILITY EXCEED THE AMOUNT YOU PAID FOR THE PRODUCT OR SERVICE GIVING RISE TO THE CLAIM.
      </p>
    </section>

    <!-- Section 9 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Indemnification</h2>
      <p class="text-gray-700 leading-relaxed">
        You agree to indemnify, defend, and hold harmless {{ $page.props.store_name }} and its affiliates, officers, directors, employees, and agents from any claims, damages, losses, liabilities, and expenses (including reasonable attorneys' fees) arising out of your violation of these Terms or your misuse of the Services.
      </p>
    </section>

    <!-- Section 10 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Governing Law & Dispute Resolution</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        These Terms shall be governed by and construed in accordance with the laws of [Your Jurisdiction], without regard to its conflict of law principles.
      </p>
      <p class="text-gray-700 leading-relaxed">
        Any dispute arising from these Terms or your use of the Services shall be resolved exclusively in the courts located in [Your Jurisdiction], and you consent to the personal jurisdiction of such courts.
      </p>
    </section>

    <!-- Section 11 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">11. Changes to These Terms</h2>
      <p class="text-gray-700 leading-relaxed">
        {{ $page.props.store_name }} reserves the right to modify these Terms at any time. Changes will be effective upon posting to this page. Your continued use of the Services after any changes constitutes your acceptance of the revised Terms. We encourage you to review this page periodically.
      </p>
    </section>

    <!-- Contact -->
    <section class="mt-16 bg-gray-50 rounded-lg p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Contact Information</h2>
      <p class="text-gray-700 leading-relaxed">
        Questions about these Terms? Contact {{ $page.props.store_name }} at:
      </p>
      <p class="mt-4 text-gray-700">
        <strong>Email:</strong> {{ $page.props.store_email }}<br />
        <strong>Address:</strong> {{ $page.props.store_address }}
      </p>
    </section>
  </div>
</template>