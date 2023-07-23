<template>
    <div id="app">
        <router-view v-slot="{ Component, route }">
            <transition :name="route.meta.transition || 'fade'">
                <component :is="Component" />
            </transition>
        </router-view>
    </div>
</template>
