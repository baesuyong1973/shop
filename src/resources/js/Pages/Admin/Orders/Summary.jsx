import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Summary({ shop, summary, userSummary, dateFrom, dateTo }) {
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

    const periodLabel = hasFilter
        ? `${dateFrom ?? '指定なし'} 〜 ${dateTo ?? '指定なし'}`
        : 'すべての期間';

    return (
        <div className="min-h-screen bg-gray-100 print:bg-white">
            <Head title="注文集計" />

            <nav className="border-b border-gray-100 bg-white print:hidden">
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

            <div className="py-12 print:py-0">
                <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 print:max-w-none print:px-0">
                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 print:shadow-none print:sm:rounded-none print:p-0">
                        <div className="mb-4 hidden items-center justify-between print:flex">
                            <h1 className="text-lg font-semibold text-gray-900">
                                {shop.name} 注文集計
                            </h1>
                            <p className="text-sm text-gray-600">
                                {periodLabel}
                            </p>
                        </div>

                        <form
                            onSubmit={submit}
                            className="mb-6 flex flex-wrap items-end gap-4 print:hidden"
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
                            <button
                                type="button"
                                onClick={() => window.print()}
                                className="ml-auto rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                印刷する
                            </button>
                        </form>

                        <p className="mb-4 text-sm text-gray-500 print:hidden">
                            {hasFilter
                                ? `${dateFrom ?? '指定なし'} 〜 ${dateTo ?? '指定なし'} の注文（キャンセルを除く）を集計しています。`
                                : 'すべての注文（キャンセルを除く）を集計しています。'}
                        </p>

                        <h2 className="mb-2 text-sm font-semibold text-gray-900">
                            商品別集計
                        </h2>

                        {summary.length === 0 ? (
                            <p className="py-6 text-center text-sm text-gray-500">
                                該当する注文がありません。
                            </p>
                        ) : (
                            <>
                                {/* スマホ表示: カード形式 */}
                                <div className="space-y-4 print:hidden sm:hidden">
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

                                {/* PC/タブレット表示・印刷: テーブル形式 */}
                                <div className="hidden overflow-x-auto print:mb-8 print:block sm:block">
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

                        <h2 className="mb-2 mt-8 text-sm font-semibold text-gray-900 print:mt-0">
                            ユーザー別集計
                        </h2>

                        {userSummary.length === 0 ? (
                            <p className="py-6 text-center text-sm text-gray-500">
                                該当する注文がありません。
                            </p>
                        ) : (
                            <>
                                {/* スマホ表示: カード形式 */}
                                <div className="space-y-4 print:hidden sm:hidden">
                                    {userSummary.map((row) => (
                                        <div
                                            key={row.user_id}
                                            className="rounded-lg border border-gray-200 p-4"
                                        >
                                            <div className="text-sm font-medium text-gray-900">
                                                {row.user_name}
                                            </div>
                                            <div className="text-xs text-gray-500">
                                                {row.user_email}
                                            </div>

                                            <ul className="mt-3 space-y-1 text-sm text-gray-700">
                                                {row.items.map((item) => (
                                                    <li
                                                        key={item.product_name}
                                                        className="flex justify-between"
                                                    >
                                                        <span>
                                                            {item.product_name}
                                                        </span>
                                                        <span>
                                                            {
                                                                item.total_quantity
                                                            }
                                                            {item.unit_name}
                                                        </span>
                                                    </li>
                                                ))}
                                            </ul>

                                            <dl className="mt-3 grid grid-cols-2 gap-y-1 border-t border-gray-100 pt-2 text-sm text-gray-700">
                                                <dt className="text-gray-500">
                                                    合計金額
                                                </dt>
                                                <dd className="text-right">
                                                    ¥
                                                    {Number(
                                                        row.total_amount,
                                                    ).toLocaleString()}
                                                </dd>
                                            </dl>
                                        </div>
                                    ))}
                                </div>

                                {/* PC/タブレット表示・印刷: テーブル形式 */}
                                <div className="hidden overflow-x-auto print:block sm:block">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                <th className="px-4 py-3">
                                                    ユーザー
                                                </th>
                                                <th className="px-4 py-3">
                                                    商品名 / 合計数量
                                                </th>
                                                <th className="px-4 py-3">
                                                    合計金額
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-200">
                                            {userSummary.map((row) => (
                                                <tr key={row.user_id}>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        {row.user_name}
                                                        <div className="text-xs text-gray-500">
                                                            {row.user_email}
                                                        </div>
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        <ul className="space-y-1">
                                                            {row.items.map(
                                                                (item) => (
                                                                    <li
                                                                        key={
                                                                            item.product_name
                                                                        }
                                                                        className="flex justify-between gap-4"
                                                                    >
                                                                        <span>
                                                                            {
                                                                                item.product_name
                                                                            }
                                                                        </span>
                                                                        <span>
                                                                            {
                                                                                item.total_quantity
                                                                            }
                                                                            {
                                                                                item.unit_name
                                                                            }
                                                                        </span>
                                                                    </li>
                                                                ),
                                                            )}
                                                        </ul>
                                                    </td>
                                                    <td className="px-4 py-3 text-sm text-gray-900">
                                                        ¥
                                                        {Number(
                                                            row.total_amount,
                                                        ).toLocaleString()}
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
