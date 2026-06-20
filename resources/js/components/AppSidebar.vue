<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Banknote,
    BookUp,
    BookDown,
    LayoutGrid,
    Notebook,
    ReceiptText,
    Users,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Anggota',
        href: '/anggota',
        icon: Users,
    },
    {
        title: 'Simpanan',
        href: '/simpanan',
        icon: BookUp,
    },
    {
        title: 'Pinjaman',
        href: '/pinjaman',
        icon: BookDown,
    },
    {
        title: 'Pembayaran',
        href: '/pembayaran-pinjaman',
        icon: Banknote,
        children: [
            {
                title: 'Daftar Pembayaran',
                href: '/pembayaran-pinjaman',
            },
            {
                title: 'Tambah Pembayaran',
                href: '/pembayaran-pinjaman/create',
            },
            {
                title: 'Jadwal',
                href: '/pembayaran-pinjaman/jadwal',
            },
        ],
    },
    {
        title: 'Pendapatan',
        href: '/pendapatan-transaksi',
        icon: ReceiptText,
        children: [
            {
                title: 'Transaksi Pendapatan',
                href: '/pendapatan-transaksi',
            },
            {
                title: 'Tambah Pendapatan',
                href: '/pendapatan-transaksi/create',
            },
            {
                title: 'Kategori Pendapatan',
                href: '/pendapatan-kategori',
            },
        ],
    },
    {
        title: 'Pengeluaran',
        href: '/pengeluaran-transaksi',
        icon: ReceiptText,
        children: [
            {
                title: 'Transaksi Pengeluaran',
                href: '/pengeluaran-transaksi',
            },
            {
                title: 'Tambah Pengeluaran',
                href: '/pengeluaran-transaksi/create',
            },
            {
                title: 'Kategori Pengeluaran',
                href: '/pengeluaran-kategori',
            },
        ],
    },
    {
        title: 'Jurnals',
        href: '/jurnals',
        icon: Notebook,
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
