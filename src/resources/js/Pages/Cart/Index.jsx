import DangerButton from '@/Components/DangerButton';
import Footer from '@/Components/Footer';
import Header from '@/Components/Header';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export default function Index({ shop, items, total, status, error }) {
    const { t } = useTranslation();
    const auth = usePage().props.auth;

    const remove = (product) => {
        if (confirm(t('cart.confirmRemove', { name: product.name }))) {
            router.delete(route('cart.destroy', [shop, product.id]));
        }
    };

    const checkout = () => {
        if (confirm(t('cart.confirmOrder'))) {
            router.post(route('orders.store', shop));
        }
    };

    return (
        <div className="flex min-h-screen flex-col bg-gray-100">
            <Head title={t('cart.title')} />

            <Header
                backHref={route('shops.show', shop)}
                backLabel={t('products.backToList')}
            />

            <div className="flex-1 py-12">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    {status && (
                        <div className="mb-4 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">
                            {status}
                        </div>
                    )}
                    {error && (
                        <div className="mb-4 rounded-md bg-red-50 p-4 text-sm font-medium text-red-700">
                            {error}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                        {items.length === 0 ? (
                            <p className="py-12 text-center text-sm text-gray-500">
                                {t('cart.empty')}
                            </p>
                        ) : (
                            <>
                                {/* スマホ表示: カード形式 */}
                                <div className="space-y-4 sm:hidden">
                                    {items.map((item) => (
                                        <div
                                            key={item.product.id}
                                            className="rounded-lg border border-gray-200 p-4"
                                        >
                                            <div className="flex items-center gap-3">
                                                <img
                                                    src={`/storage/${item.product.image_path}`}
                                                    alt={item.product.name}
                                                    className="h-16 w-16 flex-shrink-0 rounded object-cover"
                                                />
                                                <span className="text-sm font-medium text-gray-900">
                                                    {item.product.name}
                                                </span>
                                            </div>

                                            <dl className="mt-3 grid grid-cols-2 gap-y-1 text-sm text-gray-700">
                                                <dt className="text-gray-500">
                                                    {t('cart.unitPrice')}
                                                </dt>
                                                <dd className="text-right">
                                                    ¥
                                                    {Number(
                                                        item.product.price,
                                                    ).toLocaleString()}
                                                </dd>

                                                <dt className="text-gray-500">
                                                    {t('cart.unit')}
                                                </dt>
                                                <dd className="text-right">
                                                    {item.product.unit
                                                        ? `${item.product.unit_quantity ?? 1}${item.product.unit.name}`
                                                        : '-'}
                                                </dd>

                                                <dt className="text-gray-500">
                                                    {t('cart.quantity')}
                                                </dt>
                                                <dd className="text-right">
                                                    {item.quantity}
                                                </dd>

                                                <dt className="text-gray-500">
                                                    {t('cart.subtotal')}
                                                </dt>
                                                <dd className="text-right font-semibold text-gray-900">
                                                    ¥
                                                    {Number(
                                                        item.subtotal,
                                                    ).toLocaleString()}
                                                </dd>
                                            </dl>

                                            <div className="mt-3 text-right">
                                                <DangerButton
                                                    onClick={() =>
                                                        remove(item.product)
                                                    }
                                                >
                                                    {t('cart.delete')}
                                                </DangerButton>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {/* PC/タブレット表示: テーブル形式 */}
                                <div className="hidden overflow-x-auto sm:block">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                <th className="px-4 py-3">
                                                    {t('cart.product')}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t('cart.unitPrice')}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t('cart.unit')}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t('cart.quantity')}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t('cart.subtotal')}
                                                </th>
                                                <th className="px-4 py-3">
                                                    {t('cart.actions')}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200">
                                            {items.map((item) => (
                                                <tr key={item.product.id}>
                                                    <td className="px-4 py-3">
                                                        <div className="flex items-center gap-3">
                                                            <img
                                                                src={`/storage/${item.product.image_path}`}
                                                                alt={
                                                                    item
                                                                        .product
                                                                        .name
                                                                }
                                                                className="h-12 w-12 rounded object-cover"
                                                            />
                                                            <span className="text-sm text-gray-900">
                                                                {
                                                                    item
                                                                        .product
                                                                        .name
                                                                }
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        ¥
                                                        {Number(
                                                            item.product
                                                                .price,
                                                        ).toLocaleString()}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {item.product.unit
                                                            ? `${item.product.unit_quantity ?? 1}${item.product.unit.name}`
                                                            : '-'}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {item.quantity}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm font-semibold text-gray-900">
                                                        ¥
                                                        {Number(
                                                            item.subtotal,
                                                        ).toLocaleString()}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm">
                                                        <DangerButton
                                                            onClick={() =>
                                                                remove(
                                                                    item.product,
                                                                )
                                                            }
                                                        >
                                                            {t('cart.delete')}
                                                        </DangerButton>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="mt-6 flex flex-col items-stretch gap-4 sm:items-end">
                                    <div className="text-right text-xl font-bold text-gray-900">
                                        {t('cart.total', {
                                            amount: Number(
                                                total,
                                            ).toLocaleString(),
                                        })}
                                    </div>

                                    {auth.user ? (
                                        <PrimaryButton
                                            className="justify-center"
                                            onClick={checkout}
                                        >
                                            {t('cart.checkout')}
                                        </PrimaryButton>
                                    ) : (
                                        <div className="text-right">
                                            <p className="mb-2 text-sm text-gray-600">
                                                {t('cart.loginRequired')}
                                            </p>
                                            <Link
                                                href={route('login')}
                                                className="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 sm:w-auto"
                                            >
                                                {t('cart.loginToOrder')}
                                            </Link>
                                        </div>
                                    )}
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>

            <Footer />
        </div>
    );
}
