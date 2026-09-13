<template>
    <section class="my-2">
        <span class="d-block mb-5">{{ $t('Home') }}/{{ $t('Payment History') }}</span>
        <h3 class="mb-4">{{ $t('Payment History') }}</h3>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">{{ $t('Order ID') }}</th>
                        <th scope="col">{{ $t('User Name') }}</th>
                        <th scope="col">{{ $t('Course Name') }}</th>
                        <th scope="col">{{ $t('Method') }}</th>
                        <th scope="col">{{ $t('Price') }}</th>
                        <th scope="col">{{ $t('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="border-start border-end" v-if="transactions.length > 0">
                    <tr v-for="transaction in transactions" :key="transaction">
                        <td>{{ transaction.enrollment_id }}</td>
                        <td>{{ transaction.name }}</td>
                        <td>{{ shortTitle(transaction.course_title) }}</td>
                        <td>{{ transaction.payment_method }}</td>
                        <td>{{ transaction.payment_amount }}</td>
                        <td class="text-success">{{ transaction.status }}</td>
                    </tr>
                </tbody>
                <tbody class="border-start border-end" v-else>
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            {{ $t('there is no payment history found!') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<style lang="scss" scoped>
.table {
    thead {
        tr {
            th {
                background-color: #677388;
                color: white;
                padding: 1rem;
                font-weight: normal;

                &:first-child {
                    border-top-left-radius: .7rem;
                }

                &:last-child {
                    border-top-right-radius: .7rem;
                }
            }
        }
    }

    tbody {
        tr {
            td {
                padding: 1rem;
            }
        }
    }
}
</style>

<script setup>
import { useAuthStore } from '@/stores/auth'
import { ref } from 'vue';
const authStore = useAuthStore()

let transactions = ref({})

axios.get(`/transactions`, {
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer ' + authStore.authToken
    }
}).then((res) => {
    transactions.value = res.data.data.transactions
})

function shortTitle(title) {
    return title.length > 30 ? title.slice(0, 30) + '...' : title
}
</script>
