import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="min-h-screen bg-gray-100">
            <header className="bg-white shadow-sm">
                <div className="mx-auto flex h-20 max-w-5xl items-center px-6">
                    <Link href="/">
                        <ApplicationLogo className="h-12 w-12 fill-current text-gray-500" />
                    </Link>
                </div>
            </header>

            <div className="flex justify-center px-6 py-10">
                <div className="w-full max-w-md overflow-hidden bg-white px-6 py-4 shadow-md sm:rounded-lg">
                    {children}
                </div>
            </div>
        </div>
    );
}
