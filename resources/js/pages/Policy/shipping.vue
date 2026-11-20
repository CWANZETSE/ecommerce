<script setup>
import StorefrontLayout from '@/layouts/StorefrontLayout.vue'
defineOptions({ layout: StorefrontLayout })
</script>

<template>
  <div class="max-w-4xl mx-auto px-4 py-16 sm:px-6 lg:px-8">
    <!-- Header -->
    <header class="mb-12 border-b border-gray-200 pb-8">
      <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">
        Shipping Policy
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
        Thank you for shopping at {{ $page.props.store_name }}. This Shipping Policy outlines how we process orders, the third-party carriers and logistics partners we use, delivery estimates, and related terms. By placing an order, you agree to the terms described below.
      </p>
    </section>

    <!-- Section 1 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Order Processing</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        All orders are subject to a processing period before shipment. This period allows us to verify your order, prepare your items, and hand them over to our third-party shipping partners.
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li><strong>Standard Processing Time:</strong> 1–3 business days (Monday–Friday, excluding holidays).</li>
        <li><strong>Peak Seasons & Holidays:</strong> Processing may take 3–5 business days due to increased order volume.</li>
        <li><strong>Pre-Orders & Backorders:</strong> Items marked as pre-order or backorder will ship on or around the estimated date shown on the product page.</li>
      </ul>
      <p class="text-gray-700 leading-relaxed mt-4">
        You will receive a shipping confirmation email with tracking information once your order has been dispatched by our shipping partner.
      </p>
    </section>

    <!-- Section 2 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Third-Party Shipping Carriers</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        {{ $page.props.store_name }} does not operate its own delivery fleet. Instead, we partner with reputable third-party shipping carriers and logistics providers to deliver your orders. The carrier assigned to your order depends on your delivery address, the shipping method selected at checkout, and carrier availability.
      </p>
      <p class="text-gray-700 leading-relaxed mb-4">
        Our third-party shipping partners may include, but are not limited to:
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li>National and regional postal services.</li>
        <li>Commercial courier and parcel networks.</li>
        <li>Specialized freight and last-mile delivery providers.</li>
      </ul>
      <p class="text-gray-700 leading-relaxed mt-4">
        Because delivery is handled by third parties, {{ $page.props.store_name }} cannot guarantee exact delivery dates or times. Any delivery estimates provided are based on the carrier's published timelines and are not binding commitments.
      </p>
    </section>

    <!-- Section 3 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Shipping Charges (As Charged at Checkout)</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        Shipping charges are calculated in real time during checkout based on the third-party carrier's rates, your delivery address, the shipping method you select, and the size and weight of your order.
      </p>
      <div class="bg-gray-50 border-l-4 border-gray-900 p-6 my-6">
        <p class="text-gray-900 font-semibold leading-relaxed">
          The shipping charge you see at checkout is the final shipping charge for your order. {{ $page.props.store_name }} does not add hidden shipping fees after checkout. You will be charged exactly the shipping amount displayed on the checkout page before you confirm your order.
        </p>
      </div>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li><strong>Calculated at Checkout:</strong> Shipping costs are determined by our third-party carrier integrations and displayed on the checkout page before payment.</li>
        <li><strong>Final Amount:</strong> The shipping charge shown at checkout is the amount that will be charged. No additional shipping fees will be applied after your order is placed.</li>
        <li><strong>Free Shipping Promotions:</strong> If a free shipping promotion applies to your order, it will be reflected at checkout. Eligibility is determined by the promotion terms in effect at the time of purchase.</li>
        <li><strong>Currency:</strong> All shipping charges are displayed in the currency shown at checkout.</li>
      </ul>
    </section>

    <!-- Section 4 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Shipping Methods & Delivery Estimates</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        Available shipping methods and their estimated delivery windows are displayed at checkout based on your destination and the third-party carriers serving your area. Delivery times are estimates provided by the carrier and begin from the date of dispatch, not the date the order is placed.
      </p>

      <!-- Shipping Table -->
      <div class="overflow-x-auto mt-6">
        <table class="w-full border-collapse border border-gray-300 text-left text-gray-700">
          <thead class="bg-gray-100">
            <tr>
              <th class="border border-gray-300 px-4 py-3 font-semibold">Shipping Method</th>
              <th class="border border-gray-300 px-4 py-3 font-semibold">Estimated Delivery</th>
              <th class="border border-gray-300 px-4 py-3 font-semibold">Cost</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="border border-gray-300 px-4 py-3">Standard Shipping</td>
              <td class="border border-gray-300 px-4 py-3">Varies by carrier (typically 5–8 business days)</td>
              <td class="border border-gray-300 px-4 py-3">As calculated at checkout</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="border border-gray-300 px-4 py-3">Expedited Shipping</td>
              <td class="border border-gray-300 px-4 py-3">Varies by carrier (typically 2–4 business days)</td>
              <td class="border border-gray-300 px-4 py-3">As calculated at checkout</td>
            </tr>
            <tr>
              <td class="border border-gray-300 px-4 py-3">Express / Next-Day</td>
              <td class="border border-gray-300 px-4 py-3">Varies by carrier (typically 1–2 business days)</td>
              <td class="border border-gray-300 px-4 py-3">As calculated at checkout</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="text-gray-700 leading-relaxed mt-4 text-sm">
        * The methods and estimates shown above are examples. The exact options available to you, their delivery windows, and their costs are displayed at checkout and are determined by our third-party carriers. Delivery estimates are not guaranteed. Weather, carrier delays, and customs processing may extend delivery times.
      </p>
    </section>

    <!-- Section 5 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Shipping Destinations</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        {{ $page.props.store_name }} currently ships to the destinations made available at checkout through our third-party carriers. Availability depends on the service areas of our shipping partners.
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li>Domestic and international destinations as listed at checkout.</li>
        <li>Remote or restricted areas may have limited carrier coverage or extended delivery times.</li>
        <li>Some destinations may not be serviceable by our third-party carriers at any given time.</li>
      </ul>
      <p class="text-gray-700 leading-relaxed mt-4">
        If your country or region is not available at checkout, please contact our support team at <span class="font-medium text-gray-900">{{ $page.props.store_email }}</span> for assistance.
      </p>
    </section>

    <!-- Section 6 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">6. International Shipping, Duties & Taxes</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        For international orders, {{ $page.props.store_name }} ships through third-party international carriers on a DDU (Delivered Duty Unpaid) basis unless otherwise stated. This means:
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li>Import duties, taxes, and customs fees are the responsibility of the recipient.</li>
        <li>These charges are determined by your local customs authority and are not included in the purchase price or the shipping charge shown at checkout.</li>
        <li>Refusal to pay customs fees may result in the package being returned or abandoned, and refunds will not be issued for shipping costs in such cases.</li>
      </ul>
      <p class="text-gray-700 leading-relaxed mt-4">
        Customs processing may add several days to delivery times. {{ $page.props.store_name }} is not responsible for delays caused by customs authorities or third-party carriers.
      </p>
    </section>

    <!-- Section 7 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Order Tracking</h2>
      <p class="text-gray-700 leading-relaxed">
        Once your order ships, you will receive an email containing your tracking number and a link provided by our third-party carrier to track your package. You can also track your order by logging into your account on {{ $page.props.store_name }}. Please allow up to 48 hours for tracking information to update after dispatch, as tracking data is supplied by the carrier.
      </p>
    </section>

    <!-- Section 8 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Lost, Stolen, or Damaged Packages</h2>
      <p class="text-gray-700 leading-relaxed mb-4">
        Delivery is performed by third-party carriers. {{ $page.props.store_name }} is not liable for packages that are lost, stolen, or damaged after they have been handed over to the carrier. However, we are committed to helping you resolve such issues.
      </p>
      <ul class="list-disc pl-6 space-y-2 text-gray-700">
        <li><strong>Lost in Transit:</strong> If tracking shows no movement for 7+ business days (domestic) or 14+ days (international), contact us and we will initiate an investigation with the third-party carrier.</li>
        <li><strong>Damaged on Arrival:</strong> Please inspect your package upon delivery. Report any damage within 48 hours with photos of the packaging and item, and we will work with you and the carrier on a replacement or refund.</li>
        <li><strong>Stolen Packages:</strong> If tracking confirms delivery but you did not receive the package, please file a report with the carrier and local authorities, then contact us with the report details.</li>
      </ul>
    </section>

    <!-- Section 9 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Address Accuracy & Changes</h2>
      <p class="text-gray-700 leading-relaxed">
        Please ensure your shipping address is correct at checkout. {{ $page.props.store_name }} and our third-party carriers are not responsible for orders shipped to an incorrect address provided by the customer. If you need to change your address after placing an order, contact us immediately at <span class="font-medium text-gray-900">{{ $page.props.store_email }}</span>. We will do our best to request a change with the carrier, but once an order has been dispatched, we cannot modify the destination.
      </p>
    </section>

    <!-- Section 10 -->
    <section class="mb-10">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Split Shipments</h2>
      <p class="text-gray-700 leading-relaxed">
        If your order contains multiple items, they may ship separately through different third-party carriers or at different times. You will receive separate tracking numbers for each shipment. The shipping charge calculated at checkout applies to your total order and will not be duplicated for split shipments.
      </p>
    </section>

    <!-- Contact -->
    <section class="mt-16 bg-gray-50 rounded-lg p-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Questions About Shipping?</h2>
      <p class="text-gray-700 leading-relaxed">
        If you have any questions about this Shipping Policy or your order, please contact {{ $page.props.store_name }}:
      </p>
      <p class="mt-4 text-gray-700">
        <strong>Email:</strong> {{ $page.props.store_email }}<br />
        <strong>Support Hours:</strong> Monday–Friday, 9:00 AM – 5:00 PM [EAT]
      </p>
    </section>
  </div>
</template>