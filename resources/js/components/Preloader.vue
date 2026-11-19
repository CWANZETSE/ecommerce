<template>
    <div class="rbt-preloader" style="display: none;">
        <div class="rbt-preloader-inner">
            <svg class="rbt-preloader-cart" role="img" aria-label="Shopping cart line animation" viewBox="0 0 128 128"
                width="128px" height="128px" xmlns="http://www.w3.org/2000/svg">
                <g fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="8">
                    <g class="rbt-preloader-cart-track" stroke="hsla(0,10%,10%,0.1)">
                        <polyline points="4,4 21,4 26,22 124,22 112,64 35,64 39,80 106,80"></polyline>
                        <circle cx="43" cy="111" r="13"></circle>
                        <circle cx="102" cy="111" r="13"></circle>
                    </g>
                    <g class="rbt-preloader-cart-lines" stroke="currentColor">
                        <polyline class="rbt-preloader-cart-top"
                            points="4,4 21,4 26,22 124,22 112,64 35,64 39,80 106,80" stroke-dasharray="338 338"
                            stroke-dashoffset="-338"></polyline>
                        <g class="rbt-preloader-cart-wheel1" transform="rotate(-90,43,111)">
                            <circle class="rbt-preloader-cart-wheel-stroke" cx="43" cy="111" r="13"
                                stroke-dasharray="81.68 81.68" stroke-dashoffset="81.68"></circle>
                        </g>
                        <g class="rbt-preloader-cart-wheel2" transform="rotate(90,102,111)">
                            <circle class="rbt-preloader-cart-wheel-stroke" cx="102" cy="111" r="13"
                                stroke-dasharray="81.68 81.68" stroke-dashoffset="81.68"></circle>
                        </g>
                    </g>
                </g>
            </svg>
            <div class="preloader-text">
                <p class="preloader-msg">Gearing up something amazing for you…</p>
                <p class="preloader-msg preloader-msg--last">Still waiting? Magic takes a moment! ✨</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.rbt-preloader {
    background: var(--color-white);
    width: 100vw;
    height: 100vh;
    position: fixed;
    z-index: 99999;
    top: 0;
    left: 0
}

.rbt-preloader-inner {
    height: 100%;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column
}

.rbt-preloader-inner img,
.rbt-preloader-inner svg {
    max-width: 300px
}

.preloader-msg {
    animation: msg .3s 13.7s linear forwards;
    position: absolute;
    width: 100%;
    text-align: center;
    left: 0
}

.preloader-msg--last {
    animation-direction: reverse;
    animation-delay: 14s;
    visibility: hidden
}

.rbt-preloader-cart {
    display: block;
    margin: 0 auto 1.5em auto;
    width: 60px;
    height: 60px
}

.rbt-preloader-cart-lines,
.rbt-preloader-cart-top,
.rbt-preloader-cart-wheel-stroke,
.rbt-preloader-cart-wheel1,
.rbt-preloader-cart-wheel2 {
    animation: cartLines 2s ease-in-out infinite
}

.rbt-preloader-cart-lines {
    stroke: var(--primary)
}

.rbt-preloader-cart-top {
    animation-name: cartTop
}

.rbt-preloader-cart-wheel1 {
    animation-name: cartWheel1;
    transform: rotate(-.25turn);
    transform-origin: 43px 111px
}

.rbt-preloader-cart-wheel2 {
    animation-name: cartWheel2;
    transform: rotate(.25turn);
    transform-origin: 102px 111px
}

.rbt-preloader-cart-wheel-stroke {
    animation-name: cartWheelStroke
}

.rbt-preloader-cart-track {
    stroke: hsla(var(--hue), 10%, 10%, .1);
    transition: stroke var(--trans-dur)
}
</style>