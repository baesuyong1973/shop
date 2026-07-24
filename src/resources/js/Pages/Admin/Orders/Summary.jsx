import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Summary({ shop, summary, dateFrom, dateTo }) {
    const [form, setForm] = useState({
        date_from: dateFrom ?? '',
        date_to: dateTo ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        const query = Object.fromEntries(
            Object.entries(form).filter(([, v]) => v !== ''),
        );
        router.get(route('admin.shop.orders.summary', shop), query, {
            preserveState: true,
            replace: true,
        });
    };

    const clear = () => {
        setForm({ date_from: '', date_to: '' });
        router.get(
            route('admin.shop.orders.summary', shop),
            {},
            { preserveState: true, replace: true },
        );
    };

    const hasFilter = dateFrom || dateTo;

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title="注文集計" />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        注文集計
                    </div>

                    <Link
                        href={route('admin.shop.orders.index', shop)}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        注文一覧に戻る
                    </Link>
                </div>
            </nav>

            <div className="py-12">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                        <form
                            onSubmit={submit}
                            className="mb-6 flex flex-wrap items-end gap-4"
                        >
                            <div>
                                <InputLabel htmlFor="date_from" value="開始日" />
                                <TextInput
                                    id="date_from"
                                    type="date"
                                    value={form.date_from}
                                    onChange={(e) =>
                                        setForm((prev) => ({
                                            ...prev,
                                            date_from: e.target.value,
                                        }))
                                    }
                                    className="mt-1"
                                />
                            </div>
                            <div>
                                <InputLabel htmlFor="date_to" value="終了日" />
                                <TextInput
                                    id="date_to"
                                    type="date"
                                    value={form.date_to}
                                    onChange={(e) =>
                                        setForm((prev) => ({
                                            ...prev,
                                            date_to: e.target.value,
                                        }))
                                    }
                                    className="mt-1"
                                />
                            </div>
                            <button
                                type="submit"
                                className="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                            >
                                絞り込む
                            </button>
                            {hasFilter && (
                                <button
                                    type="button"
                                    onClick={clear}
                                    className="text-sm text-gray-600 underline hover:text-gray-900"
                                >
                                    すべての期間を対象にする
                                </button>
                            )}
                        </form>

                        <p className="mb-4 text-sm text-gray-500">
                            {hasFilter
                                ? `${dateFrom ?? '指定なし'} 〜 ${dateTo ?? '指定なし'} の注文（キャンセルを除く）を商品ごとに集計しています。`
                                : 'すべての注文（キャンセルを除く）を商品ごとに集計しています。'}
                        </p>

                        {summary.length === 0 ? (
                            <p className="py-6 text-center text-sm text-gray-500">
                                該当する注文がありません。
                            </p>
                        ) : (
                            <>
                                {/* スマホ表示: カード形式 */}
                                <div className="space-y-4 sm:hidden">
                                    {summary.map((row) => (
                                        <div
                                            key={row.product_name}
                                            className="rounded-lg border border-gray-200 p-4"
                                        >
                                            <div className="text-sm font-medium text-gray-900">
                                                {row.product_name}
                                            </div>

                                            <dl className="mt-3 grid grid-cols-2 gap-y-1 text-sm text-gray-700">
                                                <dt className="text-gray-500">
                                                    合計数量
                                                </dt>
                                                <dd className="text-right">
                                                    {row.total_quantity}
                                                    {row.unit_name}
                                                </dd>
                                            </dl>
                                        </div>
                                    ))}
                                </div>

                                {/* PC/タブレット表示: テーブル形式 */}
                                <div className="hidden overflow-x-auto sm:block">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                <th className="px-4 py-3">
                                                    商品名
                                                </th>
                                                <th className="px-4 py-3">
                                                    合計数量
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200">
                                            {summary.map((row) => (
                                                <tr key={row.product_name}>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {row.product_name}
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {row.total_quantity}
                                                        {row.unit_name}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
