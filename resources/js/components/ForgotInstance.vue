<template>
    <div class="container h-100">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-md-4">
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex justify-content-center">
                        <img src="/img/pixelfed-icon-white.png" width="100" height="100" alt="Pixelfed logo">
                    </div>

                    <h2 class="text-center text-white">I forget which server I joined</h2>

                    <div v-if="tab === 'loading'" class="card">
                        <div class="card-body d-flex align-items-center justify-content-center py-5">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="tab === 'search'" class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="username" class="form-label">Enter your username</label>
                                <input
                                    type="text"
                                    class="form-control form-control-lg"
                                    id="username"
                                    placeholder="Enter your username here"
                                    autofocus
                                    v-model="username">

                                <p class="help-text small text-muted mt-1">Enter only your username, without @ or the full url</p>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary fw-bold" @click="proceedCaptcha" :disabled="!username || !username.length">Next</button>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="tab === 'captcha'" class="card">
                        <div class="card-body">
                            <h4 class="fw-bold text-center">Captcha</h4>
                            <hr>

                            <div class="mb-3 d-flex justify-content-center">
                                <vue-hcaptcha
                                    :sitekey="hkey"
                                    @verify="onVerifyCaptcha"
                                />
                            </div>

                            <div class="d-flex justify-content-end">
                                <button class="btn btn-primary fw-bold" @click="handleSubmit" :disabled="!canSubmit">Search</button>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="tab === 'results'" class="card">
                        <div v-if="results && results.length" class="list-group list-group-flush" style="max-height: 30vh; overflow-y: auto;">
                            <div v-for="item in results" class="list-group-item py-4 d-flex gap-3 justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 text-break" style="font-size: 13px">
                                        Found <span class="fw-bold">&commat;{{ item.username }}</span> on
                                    </p>
                                    <p class="mb-0 text-break" style="font-size: 16px">
                                        <span class="fw-bold">{{ item.domain }}</span>
                                    </p>
                                </div>

                                <a class="btn btn-primary btn-sm fw-bold mb-0" :href="'pixelfed://login?sc=1&domain=' + item.domain" style="min-width: 90px;">This is me</a>
                            </div>
                        </div>

                        <div v-else class="card-body">
                            <p class="mb-0 text-center lead">No results found</p>
                        </div>
                    </div>

                    <div v-else-if="tab === 'invalidtoken'" class="card">
                        <div class="card-body">
                            <h4 class="fw-bold text-center">Oops!</h4>
                            <hr>
                            <p class="text-center lead">This is an internal Pixelfed service, you are probably looking for <a href="https://pixelfed.org?ref=recovery.pixelfed.org" class="text-primary fw-bold">pixelfed.org</a></p>
                        </div>
                    </div>

                    <div v-else-if="tab === 'error'" class="card">
                        <div class="card-body">
                            <h4 class="fw-bold text-center">Oops! An error occured.</h4>
                            <hr>

                            <div v-if="errors">
                                <p v-for="error in errors" class="mb-0 text-sm text-danger">{{ error[0] }}</p>
                                <hr>
                            </div>
                            <p class="mb-0 lead">Please try again in a minute.</p>
                        </div>
                    </div>

                    <div v-else-if="tab === 'ratelimit'" class="card">
                        <div class="card-body">
                            <h4 class="fw-bold text-center">Oops! You've been rate limited.</h4>
                            <hr>
                            <p class="mb-2 lead text-center">Please try again in a minute.</p>
                            <p class="mb-0 text-center small text-muted">We know this is annoying, but this helps protect against abuse. A button will appear below once you can retry again!</p>
                        </div>
                    </div>

                    <transition>
                        <p v-if="showRetry" class="text-center w-100 d-flex">
                            <a href="#" class="btn btn-dark flex-grow-1 fw-bold" @click.prevent="handleReset()">Lookup another username</a>
                        </p>
                    </transition>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
  import VueHcaptcha from '@hcaptcha/vue3-hcaptcha';
</script>

<script>
    export default {
        data() {
            return {
                username: undefined,
                tab: 'loading',
                hkey: import.meta.env.VITE_RECOVERY_CAPTCHA_SITEKEY,
                token: undefined,
                canSubmit: false,
                results: [],
                errors: [],
                showRetry: false,
                eKey: undefined,
            }
        },

        mounted() {
            let u = new URLSearchParams(window.location.search);
            if(u.has('t')) {
                this.token = u.get('t');
            }
            window.history.pushState(null, null, '/forgot-instance');
            this.validateToken()
        },

        watch: {
            tab: function(val) {
                if(['search', 'loading'].includes(val)) {
                    this.showRetry = false;
                }
            },
        },

        methods: {
            validateToken() {
                this.tab = 'loading';

                axios.post('/api/v1/token', { token: this.token })
                .then(res => {
                    this.tab = 'search';
                })
                .catch(err => {
                    if(err.response.status === 429) {
                        this.showRetry = false;
                        this.tab = 'ratelimit';
                        setTimeout(() => {
                            this.showRetry = true;
                        }, 60000);
                    } else {
                        this.$router.push('/');
                    }
                })
            },

            handleSubmit() {
                this.showRetry = false;
                axios.post('/api/v1/lookup', { username: this.username, token: this.token, ekey: this.eKey })
                .then(res => {
                    this.results = res.data;
                    this.tab = 'results';
                    this.showRetry = true;
                })
                .catch(err => {
                    switch(err.response.status) {
                        case 422:
                            this.errors = err.response.data.errors;
                            this.tab = 'error';
                            setTimeout(() => {
                                this.showRetry = true;
                            }, 1000);
                        break;

                        case 429:
                            this.tab = 'ratelimit';
                            setTimeout(() => {
                                this.showRetry = true;
                            }, 60000);
                        break;

                        case 409:
                            this.tab = 'error';
                            if(err.response.data.message) {
                                this.errors = [[err.response.data.message]];
                            }
                            setTimeout(() => {
                                this.showRetry = true;
                            }, 60000);
                        break;

                        default:
                            this.tab = 'error';
                            setTimeout(() => {
                                this.showRetry = true;
                            }, 1000);
                        break;
                    }
                })
            },

            handleReset() {
                this.username = undefined;
                this.results = [];
                this.canSubmit = false;
                this.validateToken();
            },

            proceedCaptcha() {
                this.canSubmit = false;
                this.tab = 'captcha';
            },

            onVerifyCaptcha(token, eKey) {
                this.canSubmit = true;
                this.eKey = token;
            }
        }
    }
</script>

<style lang="scss">
    .v-enter-active,
    .v-leave-active {
      transition: opacity 0.5s ease;
    }

    .v-enter-from,
    .v-leave-to {
      opacity: 0;
    }
</style>
