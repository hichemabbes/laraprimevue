<script></script>

<template>
    <div
        class="surface-ground flex align-items-center justify-content-center min-h-screen min-w-screen overflow-hidden"
    >
        <div class="flex flex-column align-items-center justify-content-center">
            <div
                class="flex align-items-top justify-content-between px-1 pt-1.5 flex-shrink-0"
            >
                <span class="inline-flex align-items-center gap-2">
                    <svg
                        width="35"
                        height="40"
                        viewBox="0 0 35 40"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M25.87 18.05L23.16 17.45L25.27 20.46V29.78L32.49 23.76V13.53L29.18 14.73L25.87 18.04V18.05ZM25.27 35.49L29.18 31.58V27.67L25.27 30.98V35.49ZM20.16 17.14H20.03H20.17H20.16ZM30.1 5.19L34.89 4.81L33.08 12.33L24.1 15.67L30.08 5.2L30.1 5.19ZM5.72 14.74L2.41 13.54V23.77L9.63 29.79V20.47L11.74 17.46L9.03 18.06L5.72 14.75V14.74ZM9.63 30.98L5.72 27.67V31.58L9.63 35.49V30.98ZM4.8 5.2L10.78 15.67L1.81 12.33L0 4.81L4.79 5.19L4.8 5.2ZM24.37 21.05V34.59L22.56 37.29L20.46 39.4H14.44L12.34 37.29L10.53 34.59V21.05L12.42 18.23L17.45 26.8L22.48 18.23L24.37 21.05ZM22.85 0L22.57 0.69L17.45 13.08L12.33 0.69L12.05 0H22.85Z"
                            fill="var(--primary-color)"
                        />
                        <path
                            d="M30.69 4.21L24.37 4.81L22.57 0.69L22.86 0H26.48L30.69 4.21ZM23.75 5.67L22.66 3.08L18.05 14.24V17.14H19.7H20.03H20.16H20.2L24.1 15.7L30.11 5.19L23.75 5.67ZM4.21002 4.21L10.53 4.81L12.33 0.69L12.05 0H8.43002L4.22002 4.21H4.21002ZM21.9 17.4L20.6 18.2H14.3L13 17.4L12.4 18.2L12.42 18.23L17.45 26.8L22.48 18.23L22.5 18.2L21.9 17.4ZM4.79002 5.19L10.8 15.7L14.7 17.14H14.74H15.2H16.85V14.24L12.24 3.09L11.15 5.68L4.79002 5.2V5.19Z"
                            fill="var(--text-color)"
                        />
                    </svg>
                    <span class="font-semibold text-2xl text-primary"
                        >Your Logo</span
                    >
                </span>
                <span> </span>
            </div>
            <div
                style="
                    border-radius: 56px;
                    padding: 0.3rem;
                    background: linear-gradient(
                        180deg,
                        var(--primary-color) 10%,
                        rgba(33, 150, 243, 0) 30%
                    );
                "
            >
                <div
                    class="w-full surface-card py-8 px-5 sm:px-8"
                    style="border-radius: 53px"
                >
                    <div class="text-center mb-5">
                        <div class="text-900 text-3xl font-medium mb-3">
                            Welcome, Asa7bi!
                        </div>
                        <span class="text-600 font-medium"
                            >Sign in to continue</span
                        >
                    </div>

                    <div>
                        <label
                            for="email1"
                            class="block text-900 text-xl font-medium mb-2"
                            >Email</label
                        >
                        <InputText
                            id="email1"
                            type="text"
                            placeholder="Email address"
                            class="w-full md:w-30rem mb-5"
                            style="padding: 1rem"
                            v-model="email"
                        />

                        <label
                            for="password1"
                            class="block text-900 font-medium text-xl mb-2"
                            >Password</label
                        >
                        <Password
                            id="password1"
                            v-model="password"
                            placeholder="Password"
                            :toggleMask="true"
                            class="w-full mb-3"
                            inputClass="w-full"
                            inputStyle="padding:1rem"
                        ></Password>

                        <div
                            class="flex align-items-center justify-content-between mb-5 gap-5"
                        >
                            <div class="flex align-items-center">
                                <Checkbox
                                    v-model="checked"
                                    id="rememberme1"
                                    binary
                                    class="mr-2"
                                ></Checkbox>
                                <label for="rememberme1">Remember me</label>
                            </div>
                            <a
                                class="font-medium no-underline ml-2 text-right cursor-pointer"
                                style="color: var(--primary-color)"
                                >Forgot password?</a
                            >
                        </div>
                        <Button
                            label="Sign In"
                            class="w-full p-3 text-xl"
                        ></Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.pi-eye {
    transform: scale(1.6);
    margin-right: 1rem;
}

.pi-eye-slash {
    transform: scale(1.6);
    margin-right: 1rem;
}
</style>
