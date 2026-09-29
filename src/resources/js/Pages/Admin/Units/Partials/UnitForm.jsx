import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';

export default function UnitForm({ shop = null, unit = null }) {
    const isEdit = unit !== null;

    const { data, setData, post, put, processing, errors } = useForm({
        name: unit?.name ?? '',
    });

    const submit = (e) => {
        e.preventDefault();

        if (shop) {
            isEdit
                ? put(route('admin.shop.units.update', [shop, unit]))
                : post(route('admin.shop.units.store', shop));
        } else {
            isEdit
                ? put(route('admin.units.update', unit))
                : post(route('admin.units.store'));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel htmlFor="name" value="単位名（例：個、箱、kg）" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    maxLength={50}
                    required
                    isFocused
                />
                <InputError className="mt-2" message={errors.name} />
                <p className="mt-2 text-sm text-gray-500">
                    {shop
                        ? `この単位は「${shop.name}」の商品でだけ選べます。`
                        : 'この単位は全店舗の商品で選べます。'}
                </p>
                {isEdit && unit.products_count > 0 && (
                    <p className="mt-1 text-sm text-gray-500">
                        この単位は{unit.products_count}
                        件の商品で使われています。変更すると、それらの商品の表示も変わります。
                    </p>
                )}
            </div>

            <div className="flex items-center gap-4">
                <PrimaryButton disabled={processing}>
                    {isEdit ? '更新する' : '登録する'}
                </PrimaryButton>
            </div>
        </form>
    );
}
