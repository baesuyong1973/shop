import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Verify({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        code: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.verify'));
    };

    const resend = () => {
        post(route('admin.verify.resend'));
    };

    return (
        <GuestLayout>
            <Head title="確認コードの入力" />

            <h1 className="mb-2 text-lg font-semibold text-gray-900">
                確認コードの入力
            </h1>

            <p className="mb-4 text-sm text-gray-600">
                登録されているメールアドレスに確認コードを送信しました。届いた6桁のコードを入力してください。
            </p>

            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="code" value="確認コード" />

                    <TextInput
                        id="code"
                        type="text"
                        name="code"
                        inputMode="numeric"
                        autoComplete="one-time-code"
                        value={data.code}
                        className="mt-1 block w-full tracking-widest"
                        isFocused={true}
                        onChange={(e) => setData('code', e.target.value)}
                    />

                    <InputError message={errors.code} className="mt-2" />
                </div>

                <div className="mt-4 flex items-center justify-between">
                    <SecondaryButton
                        type="button"
                        onClick={resend}
                        disabled={processing}
                    >
                        コードを再送する
                    </SecondaryButton>

                    <PrimaryButton disabled={processing}>
                        確認する
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
