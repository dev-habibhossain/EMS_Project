<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Activity,
    ArrowLeftRight,
    Banknote,
    BookUser,
    Boxes,
    ClipboardList,
    Factory,
    FileText,
    LayoutGrid,
    Package,
    Palette,
    Receipt,
    Settings,
    ShieldAlert,
    ShoppingCart,
    Store,
    SwatchBook,
    Truck,
    Users,
    Warehouse,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
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
import { index as activityLogs } from '@/routes/activity-logs';
import { index as batches } from '@/routes/batches';
import { index as brands } from '@/routes/brands';
import { index as challans } from '@/routes/challans';
import { index as customers } from '@/routes/customers';
import { dashboard, pos } from '@/routes';
import { index as factories } from '@/routes/factories';
import { damaged as damagedStock, index as inventory } from '@/routes/inventory';
import { index as payments } from '@/routes/payments';
import { index as products } from '@/routes/products';
import { index as purchases } from '@/routes/purchases';
import { index as qualityGrades } from '@/routes/quality-grades';
import { index as reports } from '@/routes/reports';
import { index as sales } from '@/routes/sales';
import { index as salesReturns } from '@/routes/sales-returns';
import { edit as profile } from '@/routes/profile';
import { index as shades } from '@/routes/shades';
import { index as smsLogs } from '@/routes/sms-logs';
import { index as suppliers } from '@/routes/suppliers';
import { index as tileSizes } from '@/routes/tile-sizes';
import { index as transfers } from '@/routes/transfers';
import { index as users } from '@/routes/users';
import { index as warehouses } from '@/routes/warehouses';
import type { NavGroup } from '@/types';

const navGroups: NavGroup[] = [
    {
        title: 'Work',
        items: [
            { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
            { title: 'POS', href: pos(), icon: ShoppingCart },
        ],
    },
    {
        title: 'Catalog',
        items: [
            { title: 'Products', href: products(), icon: Package },
            { title: 'Brands', href: brands(), icon: SwatchBook },
            { title: 'Factories', href: factories(), icon: Factory },
            { title: 'Sizes', href: tileSizes(), icon: Boxes },
            { title: 'Shades', href: shades(), icon: Palette },
            { title: 'Grades', href: qualityGrades(), icon: ClipboardList },
            { title: 'Batches', href: batches(), icon: Boxes },
        ],
    },
    {
        title: 'Stock',
        items: [
            { title: 'Inventory', href: inventory(), icon: Warehouse },
            { title: 'Warehouses', href: warehouses(), icon: Store },
            { title: 'Transfers', href: transfers(), icon: ArrowLeftRight, badge: 2 },
            { title: 'Damaged', href: damagedStock(), icon: ShieldAlert },
        ],
    },
    {
        title: 'Trade',
        items: [
            { title: 'Sales', href: sales(), icon: Receipt },
            { title: 'Purchases', href: purchases(), icon: Truck },
            { title: 'Challans', href: challans(), icon: FileText, badge: 4 },
            { title: 'Returns', href: salesReturns(), icon: ArrowLeftRight },
        ],
    },
    {
        title: 'Parties',
        items: [
            { title: 'Customers', href: customers(), icon: Users },
            { title: 'Suppliers', href: suppliers(), icon: BookUser },
        ],
    },
    {
        title: 'Money',
        items: [{ title: 'Payments', href: payments(), icon: Banknote }],
    },
    {
        title: 'Insights',
        items: [{ title: 'Reports', href: reports(), icon: ClipboardList }],
    },
    {
        title: 'System',
        items: [
            { title: 'Users', href: users(), icon: Users },
            { title: 'Settings', href: profile(), icon: Settings },
            { title: 'SMS log', href: smsLogs(), icon: FileText },
            { title: 'Activity', href: activityLogs(), icon: Activity },
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon">
        <SidebarHeader class="border-b border-[#2a2622] px-2 py-3">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="hover:bg-[#2a2622]">
                        <Link :href="dashboard()" class="gap-2">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="gap-0 overflow-y-auto">
            <NavMain :groups="navGroups" />
        </SidebarContent>

        <SidebarFooter class="border-t border-[#2a2622]">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
