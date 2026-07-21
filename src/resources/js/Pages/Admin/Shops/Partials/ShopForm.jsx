import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function ShopForm({ shop = null }) {
    const isEdit = shop !== null;

    const { data, setData, post, transform, processing, errors } = useForm({
        name: shop?.name ?? '',
        slug: shop?.slug ?? '',
        address: shop?.address ?? '',
        phone: shop?.phone ?? '',
        logo: null,
        is_active: shop?.is_active ?? true,
    });

    const [preview, setPreview] = useState(
        shop?.logo_path ? `/storage/${shop.logo_path}` : null,
    );

    const handleLogoChange = (e) => {
        const file = e.target.files[0] ?? null;
        setData('logo', file);
        setPreview(file ? URL.createObjectURL(file) : null);
    };

    const submit = (e) => {
        e.preventDefault();

        if (isEdit) {
            transform((data) => ({ ...data, _method: 'put' }));
            post(route('admin.shops.update', shop), {
                forceFormData: true,
            });
        } else {
            post(route('admin.shops.store'), { forceFormData: true });
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel htmlFor="name" value="店舗名" />
                <TextInput
                    id="name"
                    className="mt-1 block w-full"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    required
                />
                <InputError className="mt-2" message={errors.name} />
            </div>

            <div>
                <InputLabel
                    htmlFor="slug"
                    value="スラッグ（URLに使用されます）"
                />
                <TextInput
                    id="slug"
                    className="mt-1 block w-full"
                    value={data.slug}
                    onChange={(e) => setData('slug', e.target.value)}
                    required
                />
                <InputError className="mt-2" message={errors.slug} />
            </div>

            <div>
                <InputLabel htmlFor="address" value="住所" />
                <TextInput
                    id="address"
                    className="mt-1 block w-full"
                    value={data.address}
                    onChange={(e) => setData('address', e.target.value)}
                />
                <InputError className="mt-2" message={errors.address} />
            </div>

            <div>
                <InputLabel htmlFor="phone" value="電話番号" />
                <TextInput
                    id="phone"
                    className="mt-1 block w-full"
                    value={data.phone}
                    onChange={(e) => setData('phone', e.target.value)}
                />
                <InputError className="mt-2" message={errors.phone} />
            </div>

            <div>
                <InputLabel htmlFor="logo" value="ロゴ画像" />
                <input
                    id="logo"
                    type="file"
                    accept="image/*"
                    className="mt-1 block w-full text-sm text-gray-700"
                    onChange={handleLogoChange}
                />
                {preview && (
                    <img
                        src={preview}
                        alt="プレビュー"
                        className="mt-2 h-24 w-24 rounded object-cover"
                    />
                )}
                <InputError className="mt-2" message={errors.logo} />
            </div>

            <div className="flex items-center">
                <Checkbox
                    id="is_active"
                    checked={data.is_active}
                    onChange={(e) => setData('is_active', e.target.checked)}
                />
                <InputLabel
                    htmlFor="is_active"
                    value="営業中として公開する"
                    className="ml-2"
                />
            </div>

            <div className="flex items-center gap-4">
                <PrimaryButton disabled={processing}>
                    {isEdit ? '更新する' : '登録する'}
                </PrimaryButton>
            </div>
        </form>
    );
}
