// Admin-facing summary of an order's deposit (admin screens are Japanese-only).
const LABELS = {
    pending: { text: '前払い待ち', className: 'bg-yellow-100 text-yellow-800' },
    paid: { text: '前払い済み', className: 'bg-green-100 text-green-800' },
    refunded: { text: '前払い返金済み', className: 'bg-gray-100 text-gray-700' },
    expired: { text: '未払いで取消', className: 'bg-red-100 text-red-800' },
};

/**
 * Renders nothing for orders that never required a deposit.
 */
export default function DepositBadge({ order, showRemaining = false }) {
    const label = LABELS[order.payment_status];

    if (!label) {
        return null;
    }

    return (
        <span className="inline-flex flex-wrap items-center gap-1">
            <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${label.className}`}>
                {label.text} ¥{Number(order.deposit_amount).toLocaleString()}
            </span>
            {showRemaining && order.payment_status === 'paid' && (
                <span className="text-xs text-gray-600">
                    店頭で ¥{Number(order.remaining_amount).toLocaleString()}
                </span>
            )}
        </span>
    );
}
