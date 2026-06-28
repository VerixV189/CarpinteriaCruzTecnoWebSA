<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

const { theme } = useAppearance();

const emojis = [
    '🧸', '🎨', '🎉', '🍭', '🧩', '🚀', '⭐', '🎈', '🎡', '🦕', 
    '🦁', '🦄', '🚗', '🍕', '🍩', '🍦', '🎮', '🌈', '🐱', '🐶',
    '🐸', '🦉', '🦋', '🍬', '🍿', '🧁', '🚒', '🚀', '🛸', '🎪'
];

const currentEmoji = ref('✨');
const isBouncing = ref(false);

const changeEmoji = () => {
    isBouncing.value = true;
    const randomIndex = Math.floor(Math.random() * emojis.length);
    currentEmoji.value = emojis[randomIndex];
    
    setTimeout(() => {
        isBouncing.value = false;
    }, 600);
};

onMounted(() => {
    changeEmoji();
});
</script>

<template>
    <span 
        v-if="theme === 'ninos'"
        @click="changeEmoji"
        :class="[
            'inline-block select-none cursor-pointer text-xl transition-all duration-300 hover:scale-125 hover:rotate-12 filter drop-shadow-md active:scale-95 mx-1',
            { 'animate-bounce-custom': isBouncing }
        ]"
        title="¡Hazme click para cambiar de emoji!"
    >
        {{ currentEmoji }}
    </span>
</template>

<style scoped>
@keyframes custom-bounce {
    0%, 100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-8px) scale(1.15) rotate(15deg);
    }
}

.animate-bounce-custom {
    animation: custom-bounce 0.5s ease-in-out;
}
</style>
