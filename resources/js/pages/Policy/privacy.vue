<script setup>
import StorefrontLayout from '@/layouts/StorefrontLayout.vue'
defineOptions({ layout: StorefrontLayout })
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
    <!-- Header -->
    <header class="mb-12 border-b border-gray-200 pb-8">
      <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">
        Privacy Policy
      </h1>
      <p class="mt-4 text-lg text-gray-600">
        {{ $page.props.store_name }}
      </p>
      <p class="mt-1 text-sm text-gray-500">
        Last Updated: November 20, 2026
      </p>
    </header>

    <!-- Intro -->
    <section class="mb-10">
      <p class="text-gray-700 leading-relaxed">
        At {{ $page.props.store_name }}, we are committed to protecting your privacy and safeguarding your personal information. This Privacy Policy explains how we collect, use, disclose, and retain your information when you visit our website, make a purchase, or interact with our services. By using {{ $page.props.store_name }}, you consent to the practices described in this policy.
      </p>
    </section>

    <!-- Section 1 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Information We Collect</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        We collect information to provide better services to all our customers. The types of information we collect depend on how you interact with {{ $page.props.store_name }}.
      </p>
      <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-2">Information You Provide Directly</h3>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li><strong>Account & Contact Data:</strong> Name, email address, phone number, shipping and billing addresses.</li>
        <li><strong>Payment Information:</strong> Credit/debit card details or other payment credentials processed securely by our payment partners. We do not store full card numbers on our servers.</li>
        <li><strong>Order & Transaction Data:</strong> Products purchased, order history, returns, and customer service communications.</li>
        <li><strong>Content You Submit:</strong> Reviews, ratings, photos, and any other content you choose to share.</li>
      </ul>
      <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-2">Information Collected Automatically</h3>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li><strong>Device & Usage Data:</strong> IP address, browser type, operating system, pages viewed, time spent on pages, and referring URLs.</li>
        <li><strong>Cookies & Similar Technologies:</strong> We use cookies, pixels, and local storage to recognize your device, remember preferences, and analyze site traffic.</li>
        <li><strong>Location Data:</strong> Approximate location derived from your IP address for shipping estimates and fraud prevention.</li>
      </ul>
    </section>

    <!-- Section 2 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">2. How We Use Your Information</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        {{ $page.props.store_name }} uses your personal information for the following purposes:
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li><strong>Fulfillment:</strong> Processing orders, arranging shipping, and providing customer support.</li>
        <li><strong>Personalization:</strong> Tailoring product recommendations, content, and advertising to your interests.</li>
        <li><strong>Communication:</strong> Sending transactional emails (order confirmations, shipping updates) and, with your consent, marketing communications.</li>
        <li><strong>Security & Fraud Prevention:</strong> Detecting and preventing fraudulent transactions, abuse, and unauthorized access.</li>
        <li><strong>Legal Compliance:</strong> Meeting tax, accounting, and regulatory obligations.</li>
        <li><strong>Improvement:</strong> Analyzing usage patterns to enhance our website, products, and services.</li>
      </ul>
    </section>

    <!-- Section 3 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Sharing Your Information</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        We do not sell your personal information. We may share your data with trusted third parties who assist us in operating {{ $page.props.store_name }}:
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li><strong>Service Providers:</strong> Payment processors, shipping carriers, email service providers, and cloud hosting partners.</li>
        <li><strong>Analytics Partners:</strong> Tools like Google Analytics to understand site performance and user behavior.</li>
        <li><strong>Legal Authorities:</strong> When required by law, subpoena, or to protect the rights, property, or safety of {{ $page.props.store_name }}, our customers, or others.</li>
        <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets, your information may be transferred as part of the transaction.</li>
      </ul>
    </section>

    <!-- Section 4 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Data Retention & Security</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        We retain your personal information only as long as necessary to fulfill the purposes outlined in this policy, comply with legal obligations, resolve disputes, and enforce our agreements.
      </p>
      <p class="text-gray-700 leading-relaxed">
        We implement industry-standard administrative, technical, and physical safeguards—including encryption (SSL/TLS), firewalls, and access controls—to protect your data. However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.
      </p>
    </section>

    <!-- Section 5 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Your Rights & Choices</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        Depending on your location, you may have the following rights regarding your personal information:
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li><strong>Access & Portability:</strong> Request a copy of the personal data we hold about you.</li>
        <li><strong>Correction:</strong> Update or correct inaccurate information through your account settings or by contacting us.</li>
        <li><strong>Deletion:</strong> Request deletion of your personal data, subject to legal retention requirements.</li>
        <li><strong>Opt-Out:</strong> Unsubscribe from marketing emails using the link in any email, or adjust cookie preferences in your browser.</li>
      </ul>
      <p class="text-gray-700 leading-relaxed mt-4">
        To exercise these rights, please contact us at <span class="font-medium text-gray-900">{{ $page.props.store_email }}</span>.
      </p>
    </section>

    <!-- Section 6 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Cookies & Tracking</h2>
      <p class="text-gray-700 leading-relaxed">
        {{ $page.props.store_name }} uses cookies and similar tracking technologies to enhance your browsing experience, analyze site traffic, and deliver personalized content. You can control cookies through your browser settings. Disabling cookies may limit certain features of the site, such as remembering your cart or login state.
      </p>
    </section>

    <!-- Section 7 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Children's Privacy</h2>
      <p class="text-gray-700 leading-relaxed">
        {{ $page.props.store_name }} is not directed to individuals under the age of 13 (or 16 in certain jurisdictions). We do not knowingly collect personal information from children. If we become aware that a child has provided us with personal data, we will take steps to delete it promptly.
      </p>
    </section>

    <!-- Section 8 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Changes to This Policy</h2>
      <p class="text-gray-700 leading-relaxed">
        We may update this Privacy Policy from time to time. The "Last Updated" date at the top of this page reflects the most recent revision. We encourage you to review this policy periodically. Your continued use of {{ $page.props.store_name }} after changes are posted constitutes acceptance of the updated policy.
      </p>
    </section>

    <!-- Contact -->
    <section class="mt-16 bg-gray-50 rounded-lg p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Contact Us</h2>
      <p class="text-gray-700 leading-relaxed">
        If you have questions about this Privacy Policy or our data practices, please contact {{ $page.props.store_name }} at:
      </p>
      <p class="mt-4 text-gray-700">
        <strong>Email:</strong> {{ $page.props.store_email }}<br />
        <strong>Address:</strong> {{ $page.props.store_address }}
      </p>
    </section>
  </div>
</template>