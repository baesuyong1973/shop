import { router } from '@inertiajs/react';
import jsQR from 'jsqr';
import { useEffect, useRef, useState } from 'react';
import Modal from './Modal';
import SecondaryButton from './SecondaryButton';

export default function QrScannerModal({
    show,
    onClose,
    scanRouteName,
    scanRouteParams,
}) {
    const videoRef = useRef(null);
    const canvasRef = useRef(document.createElement('canvas'));
    const streamRef = useRef(null);
    const frameRef = useRef(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (!show) {
            return;
        }

        setError(null);

        navigator.mediaDevices
            ?.getUserMedia({ video: { facingMode: 'environment' } })
            .then((stream) => {
                streamRef.current = stream;
                if (videoRef.current) {
                    videoRef.current.srcObject = stream;
                    videoRef.current.play();
                }
                frameRef.current = requestAnimationFrame(scanFrame);
            })
            .catch(() => {
                setError(
                    'カメラを利用できませんでした。ブラウザのカメラ権限を確認してください。',
                );
            });

        return () => {
            if (frameRef.current) {
                cancelAnimationFrame(frameRef.current);
            }
            streamRef.current?.getTracks().forEach((track) => track.stop());
            streamRef.current = null;
        };
    }, [show]);

    const scanFrame = () => {
        const video = videoRef.current;
        const canvas = canvasRef.current;

        if (video && video.readyState === video.HAVE_ENOUGH_DATA) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = context.getImageData(
                0,
                0,
                canvas.width,
                canvas.height,
            );
            const code = jsQR(imageData.data, canvas.width, canvas.height);

            if (code?.data) {
                router.get(
                    route(scanRouteName, scanRouteParams),
                    { token: code.data },
                    { onFinish: onClose },
                );
                return;
            }
        }

        frameRef.current = requestAnimationFrame(scanFrame);
    };

    return (
        <Modal show={show} onClose={onClose} maxWidth="md">
            <div className="p-6">
                <h2 className="text-lg font-medium text-gray-900">
                    会員QRコードをスキャン
                </h2>

                {error ? (
                    <p className="mt-4 text-sm text-red-600">{error}</p>
                ) : (
                    <video
                        ref={videoRef}
                        className="mt-4 w-full rounded-md bg-black"
                        muted
                        playsInline
                    />
                )}

                <div className="mt-6 flex justify-end">
                    <SecondaryButton onClick={onClose}>
                        閉じる
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    );
}
