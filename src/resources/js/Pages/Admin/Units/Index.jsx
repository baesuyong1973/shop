import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { Head, Link, router } from '@inertiajs/react';

/**
 * Without `shop`: the shared units, managed by super admins.
 * With `shop`: that shop's own units, plus the shared units read-only.
 */
export default function Index({ shop, sharedUnits = [], units, status, error }) {
    const isScoped = !!shop;
    const unitRoute = (name, unit) =>
        isScoped
            ? route(`admin.shop.units.${name}`, unit ? [shop, unit] : shop)
            : route(`admin.units.${name}`, unit);

    const destroy = (unit) => {
        if (confirm(`「${unit.name}」を削除しますか？`)) {
            router.delete(unitRoute('destroy', unit));
        }
    };

    const title = isScoped ? `単位管理（${shop.name}）` : '単位管理（共通）';

    return (
        <div className="min-h-screen bg-gray-100">
            <Head title={title} />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div className="text-lg font-semibold text-gray-900">
                        {title}
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
                <div className="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
                    {status && (
                        <div className="rounded-md bg-green-50 p-4 text-sm font-medium text-green-700">
                            {status}
                        </div>
                    )}
                    {error && (
                        <div className="rounded-md bg-red-50 p-4 text-sm font-medium text-red-700">
                            {error}
                        </div>
                    )}

                    {isScoped && (
                        <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                            <h2 className="text-base font-semibold text-gray-900">
                                共通の単位
                            </h2>
                            <p className="mt-1 text-sm text-gray-500">
                                全店舗で使える単位です。追加・変更は本部（スーパー管理者）が行います。
                            </p>
                            {sharedUnits.length === 0 ? (
                                <p className="mt-3 text-sm text-gray-500">
                                    ありません。
                                </p>
                            ) : (
                                <ul className="mt-3 flex flex-wrap gap-2">
                                    {sharedUnits.map((unit) => (
                                        <li
                                            key={unit.id}
                                            className="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700"
                                        >
                                            {unit.name}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    )}

                    <div className="overflow-hidden bg-white p-4 shadow-sm sm:rounded-lg sm:p-6">
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-4">
                            <div>
                                {isScoped && (
                                    <h2 className="text-base font-semibold text-gray-900">
                                        この店舗の単位
                                    </h2>
                                )}
                                <p className="mt-1 text-sm text-gray-500">
                                    {isScoped
                                        ? 'この店舗の商品でだけ使える単位です。'
                                        : '全店舗の商品の登録・編集画面で選べる単位です。店舗ごとの単位は各店舗の管理者が追加できます。'}
                                </p>
                            </div>
                            <Link href={unitRoute('create')}>
                                <PrimaryButton>単位を追加する</PrimaryButton>
                            </Link>
                        </div>

                        {units.length === 0 ? (
                            <p className="py-8 text-center text-sm text-gray-500">
                                単位が登録されていません。
                            </p>
                        ) : (
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr className="text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        <th className="px-4 py-3">単位名</th>
                                        <th className="px-4 py-3">
                                            使用中の商品数
                                        </th>
                                        <th className="px-4 py-3">操作</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {units.map((unit) => (
                                        <tr key={unit.id}>
                                            <td className="px-4 py-3 text-sm font-medium text-gray-900">
                                                {unit.name}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-700">
                                                {unit.products_count}件
                                            </td>
                                            <td className="px-4 py-3 text-sm">
                                                <div className="flex gap-2">
                                                    <Link
                                                        href={unitRoute(
                                                            'edit',
                                                            unit,
                                                        )}
                                                    >
                                                        <SecondaryButton>
                                                            編集
                                                        </SecondaryButton>
                                                    </Link>
                                                    <DangerButton
                                                        onClick={() =>
                                                            destroy(unit)
                                                        }
                                                        disabled={
                                                            unit.products_count >
                                                            0
                                                        }
                                                        title={
                                                            unit.products_count >
                                                            0
                                                                ? '商品で使用中のため削除できません'
                                                                : undefined
                                                        }
                                                    >
                                                        削除
                                                    </DangerButton>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
