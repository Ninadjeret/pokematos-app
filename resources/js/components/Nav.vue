<template>
<nav id="main" class="navbar">
    <ul>
        <li v-for="link in links">
            <a :href="link.url" :class="{active : currentLocation == link.url }">
                <i aria-hidden="true" class="material-icons">{{ link.icon }}</i>
                <span>{{ link.text }}</span>
            </a>
        </li>
    </ul>
</nav>
</template>

<script>
export default {
    data() {
        return {
            currentLocation: window.location.pathname,
            links: [{
                    text: 'Map',
                    url: '/',
                    icon: 'map'
                },
                {
                    text: 'Liste',
                    url: '/list',
                    icon: 'notifications_active'
                },
                {
                    text: 'Profil',
                    url: '/profile',
                    icon: 'person'
                },
            ]
        }
    }
}
</script>
