import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { formatDate } from '@/Utils/date';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ shop, products, shops, status }) {
    const isScoped = !!shop;
    const [copyTarget, setCopyTarget] = useState(null);

    const { data, setData, post, processing, errors, reset, clearErrors } =
        useForm({ shop_id: '' });

    const destroy = (product) => {
        if (confirm(`「${product.name}」を削除しますか？`)) {
            router.delete(
                route('admin.shop.products.destroy', [shop, product.id]),
            );
        }
    };

    const openCopyModal = (product) => {
        setCopyTarget(product);
        setData('shop_id', '');
        clearErrors();
    };

    const closeCopyModal = () => {
        setCopyTarget(null);
        reset();
        clearErrors();
    };

    const submitCopy = (e) => {
        e.preventDefault();
        post(route('admin.products.copy', copyTarget.id), {
            preserveScroll: true,
            onSuccess: () => closeCopyModal(),
        });
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="商品一覧" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        商品一覧{!isScoped && '（全店舗）'}
                    </div>

                    <Link
                        href={route('admin.dashboard')}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        管理画面トップに戻る
                    </Link>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {status && (
                        <div className="mb-4 rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">
                            {status}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        {isScoped && (
                            <div className="mb-4 flex justify-end">
                                <Link
                                    href={route(
                                        'admin.shop.products.create',
                                        shop,
                                    )}
                                >
                                    <PrimaryButton>
                                        商品を登録する
                                    </PrimaryButton>
                                </Link>
                            </div>
                        )}

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        <th className="px-4 py-3">画像</th>
                                        {!isScoped && (
                                            <th className="px-4 py-3">
                                                店舗
                                            </th>
                                        )}
                                        <th className="px-4 py-3">商品名</th>
                                        <th className="px-4 py-3">価格</th>
                                        <th className="px-4 py-3">単位</th>
                                        <th className="px-4 py-3">在庫</th>
                                        <th className="px-4 py-3">産地</th>
                                        <th className="px-4 py-3">入荷日</th>
                                        <th className="px-4 py-3">状態</th>
                                        <th className="px-4 py-3">操作</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {products.data.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan={isScoped ? 9 : 10}
                                                className="px-4 py-6 text-center text-sm text-gray-500"
                                            >
                                                登録されている商品がありません。
                                            </td>
                                        </tr>
                                    )}

                                    {products.data.map((product) => (
                                        <tr key={product.id}>
                                            <td className="px-4 py-3">
                                                <img
                                                    src={`/storage/${product.image_path}`}
                                                    alt={product.name}
                                                    className="h-12 w-12 rounded object-cover"
                                                />
                                            </td>
                                            {!isScoped && (
                                                <td className="px-4 py-3 text-sm text-gray-900">
                                                    {product.shop?.name}
                                                </td>
                                            )}
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {product.name}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                ¥{Number(product.price).toLocaleString()}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {product.unit
                                                    ? `${product.unit_quantity ?? 1}${product.unit.name}`
                                                    : '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {product.stock}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {product.prefecture?.name ?? '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-900">
                                                {product.arrival_date
                                                    ? formatDate(
                                                          product.arrival_date,
                                                      )
                                                    : '-'}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                {product.is_active ? (
                                                    <span className="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                                        公開中
                                                    </span>
                                                ) : (
                                                    <span className="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                                                        非公開
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex gap-2">
                                                    {isScoped && (
                                                        <>
                                                            <Link
                                                                href={route(
                                                                    'admin.shop.products.edit',
                                                                    [
                                                                        shop,
                                                                        product.id,
                                                                    ],
                                                                )}
                                                            >
                                                                <SecondaryButton>
                                                                    編集
                                                                </SecondaryButton>
                                                            </Link>
                                                            <DangerButton
                                                                onClick={() =>
                                                                    destroy(
                                                                        product,
                                                                    )
                                                                }
                                                            >
                                                                削除
                                                            </DangerButton>
                                                        </>
                                                    )}
                                                    {!isScoped && (
                                                        <SecondaryButton
                                                            onClick={() =>
                                                                openCopyModal(
                                                                    product,
                                                                )
                                                            }
                                                        >
                                                            コピー
                                                        </SecondaryButton>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>

                        <Pagination links={products.links} />
                    </div>
                </div>
            </div>

            <Modal show={copyTarget !== null} onClose={closeCopyModal} maxWidth="sm">
                <form onSubmit={submitCopy} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900">
                        商品をコピー
                    </h2>

                    <p className="mt-1 text-sm text-gray-600">
                        「{copyTarget?.name}」をコピーします。コピー先の店舗を選択してください。コピーされた商品は非公開の状態で登録されます。
                    </p>

                    <div className="mt-4">
                        <InputLabel htmlFor="copy_shop_id" value="コピー先の店舗" />
                        <select
                            id="copy_shop_id"
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value={data.shop_id}
                            onChange={(e) => setData('shop_id', e.target.value)}
                        >
                            <option value="">選択してください</option>
                            {shops?.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.name}
                                </option>
                            ))}
                        </select>
                        <InputError className="mt-2" message={errors.shop_id} />
                    </div>

                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton type="button" onClick={closeCopyModal}>
                            キャンセル
                        </SecondaryButton>
                        <PrimaryButton disabled={processing || !data.shop_id}>
                            コピーする
                        </PrimaryButton>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
