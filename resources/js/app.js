import ApexCharts from 'apexcharts';
import { Splide } from '@splidejs/splide';
import '@splidejs/splide/css';

window.ApexCharts = ApexCharts;

document.addEventListener('livewire:navigated', () => {
    document.querySelectorAll('.hero-slider').forEach((slider) => {
        if (slider.splide) {
            slider.splide.destroy();
        }

        slider.splide = new Splide(slider, {

    type: 'loop',

    perPage: 1,

    autoplay: true,

    interval: 5000,

    pauseOnHover: true,

    arrows: true,

    pagination: true,

    rewind: true,

    speed: 900,

}).mount();
    });
});