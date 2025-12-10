<template>
    <div class="pagination">
        <Button
            v-for="page in totalPages"
            :key="page"
            :label="String(page)"
            :class="{ 'p-highlight': page === currentPage }"
            @click="$emit('page-changed', page)"
        />
    </div>
</template>

<script>
import Button from 'primevue/button';

export default {
    props: {
        currentPage: {
            type: Number,
            required: true,
        },
        totalPages: {
            type: Number,
            required: true,
        },
    },
    emits: ['page-changed'],
    components: {
        Button,
    },
};
</script>

<style scoped>
.pagination {
    display: flex;
    gap: 5px;
    justify-content: center;
    margin-top: 20px;
}
</style>
