import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';

export default function OrderStatusForm({ orderStatus = null, statuses }) {
    const isEdit = orderStatus !== null;

    const otherStatuses = statuses.filter((s) => s.id !== orderStatus?.id);

    const { data, setData, post, put, transform, processing, errors } =
        useForm({
            key: orderStatus?.key ?? '',
            label: orderStatus?.label ?? '',
            sort_order: orderStatus?.sort_order ?? 0,
            is_initial: orderStatus?.is_initial ?? false,
            is_void: orderStatus?.is_void ?? false,
            next_status_ids: orderStatus?.next_statuses?.map((s) => s.id) ?? [],
        });

    const toggleNextStatus = (id) => {
        setData(
            'next_status_ids',
            data.next_status_ids.includes(id)
                ? data.next_status_ids.filter((v) => v !== id)
                : [...data.next_status_ids, id],
        );
    };

    const submit = (e) => {
        e.preventDefault();

        if (isEdit) {
            transform(({ key, ...rest }) => rest);
            put(route('admin.order-statuses.update', orderStatus));
        } else {
            post(route('admin.order-statuses.store'));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel htmlFor="key" value="キー（半角英数字・アンダースコア）" />
                {isEdit ? (
                    <p className="mt-1 rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-600">
                        {orderStatus.key}
                        <span className="ml-2 text-xs text-gray-400">
                            （登録後は変更できません）
                        </span>
                    </p>
                ) : (
                    <>
                        <TextInput
                            id="key"
                            className="mt-1 block w-full"
                            value={data.key}
                            onChange={(e) => setData('key', e.target.value)}
                            required
                        />
                        <InputError className="mt-2" message={errors.key} />
                    </>
                )}
            </div>

            <div>
                <InputLabel htmlFor="label" value="表示名" />
                <TextInput
                    id="label"
                    className="mt-1 block w-full"
                    value={data.label}
                    onChange={(e) => setData('label', e.target.value)}
                    required
                />
                <InputError className="mt-2" message={errors.label} />
            </div>

            <div>
                <InputLabel htmlFor="sort_order" value="並び順" />
                <TextInput
                    id="sort_order"
                    type="number"
                    className="mt-1 block w-full"
                    value={data.sort_order}
                    onChange={(e) => setData('sort_order', e.target.value)}
                    required
                />
                <InputError className="mt-2" message={errors.sort_order} />
            </div>

            <div className="flex items-center">
                <Checkbox
                    id="is_initial"
                    checked={data.is_initial}
                    onChange={(e) => setData('is_initial', e.target.checked)}
                />
                <InputLabel
                    htmlFor="is_initial"
                    value="新規注文の初期状態にする"
                    className="ml-2"
                />
            </div>
            <InputError message={errors.is_initial} />

            <div className="flex items-center">
                <Checkbox
                    id="is_void"
                    checked={data.is_void}
                    onChange={(e) => setData('is_void', e.target.checked)}
                />
                <InputLabel
                    htmlFor="is_void"
                    value="この状態になったら在庫を戻し、準備一覧・集計から除外する（キャンセル相当）"
                    className="ml-2"
                />
            </div>

            <div>
                <InputLabel value="遷移可能な次の状態" />
                {otherStatuses.length === 0 ? (
                    <p className="mt-1 text-sm text-gray-500">
                        他に状態が登録されていません。
                    </p>
                ) : (
                    <div className="mt-2 space-y-2">
                        {otherStatuses.map((s) => (
                            <div key={s.id} className="flex items-center">
                                <Checkbox
                                    id={`next-status-${s.id}`}
                                    checked={data.next_status_ids.includes(
                                        s.id,
                                    )}
                                    onChange={() => toggleNextStatus(s.id)}
                                />
                                <InputLabel
                                    htmlFor={`next-status-${s.id}`}
                                    value={`${s.label}（${s.key}）`}
                                    className="ml-2"
                                />
                            </div>
                        ))}
                    </div>
                )}
                <InputError className="mt-2" message={errors.next_status_ids} />
            </div>

            <div className="flex items-center gap-4">
                <PrimaryButton disabled={processing}>
                    {isEdit ? '更新する' : '登録する'}
                </PrimaryButton>
            </div>
        </form>
    );
}
