import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useForm } from '@inertiajs/react';

export default function AdminForm({ admin = null, shops = [] }) {
    const isEdit = admin !== null;

    const { data, setData, post, transform, processing, errors } = useForm({
        name: admin?.name ?? '',
        email: admin?.email ?? '',
        password: '',
        password_confirmation: '',
        role: admin?.role ?? 'shop_admin',
        shop_id: admin?.shop_id ?? '',
    });

    const submit = (e) => {
        e.preventDefault();

        if (isEdit) {
            transform((data) => ({ ...data, _method: 'put' }));
            post(route('admin.admins.update', admin.id));
        } else {
            post(route('admin.admins.store'));
        }
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div>
                <InputLabel htmlFor="name" value="名前" />
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
                <InputLabel htmlFor="email" value="メールアドレス" />
                <TextInput
                    id="email"
                    type="email"
                    className="mt-1 block w-full"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    required
                />
                <InputError className="mt-2" message={errors.email} />
            </div>

            <div>
                <InputLabel
                    htmlFor="password"
                    value={
                        isEdit
                            ? 'パスワード（変更する場合のみ入力）'
                            : 'パスワード'
                    }
                />
                <TextInput
                    id="password"
                    type="password"
                    className="mt-1 block w-full"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    required={!isEdit}
                />
                <InputError className="mt-2" message={errors.password} />
            </div>

            <div>
                <InputLabel
                    htmlFor="password_confirmation"
                    value="パスワード（確認）"
                />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    className="mt-1 block w-full"
                    value={data.password_confirmation}
                    onChange={(e) =>
                        setData('password_confirmation', e.target.value)
                    }
                    required={!isEdit}
                />
                <InputError
                    className="mt-2"
                    message={errors.password_confirmation}
                />
            </div>

            <div>
                <InputLabel htmlFor="role" value="権限" />
                <select
                    id="role"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    value={data.role}
                    onChange={(e) => setData('role', e.target.value)}
                >
                    <option value="shop_admin">店舗管理者</option>
                    <option value="super_admin">スーパー管理者</option>
                </select>
                <InputError className="mt-2" message={errors.role} />
            </div>

            {data.role === 'shop_admin' && (
                <div>
                    <InputLabel htmlFor="shop_id" value="担当店舗" />
                    <select
                        id="shop_id"
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        value={data.shop_id}
                        onChange={(e) => setData('shop_id', e.target.value)}
                    >
                        <option value="">選択してください</option>
                        {shops.map((shop) => (
                            <option key={shop.id} value={shop.id}>
                                {shop.name}
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.shop_id} />
                </div>
            )}

            <div className="flex items-center gap-4">
                <PrimaryButton disabled={processing}>
                    {isEdit ? '更新する' : '登録する'}
                </PrimaryButton>
            </div>
        </form>
    );
}
