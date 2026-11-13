<!DOCTYPE html>
<html lang="id">
<body style="margin:0;padding:24px 16px;background-color:#f3f1ea;font-family:-apple-system,'Segoe UI',Arial,sans-serif;color:#20261f;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;margin:0 auto;">
        <tr>
            <td style="background-color:#ffffff;border:1px solid #d9d5c7;border-radius:8px;padding:28px;">
                <p style="margin:0 0 16px;font-size:20px;font-weight:600;line-height:1.3;">Pengajuan izin</p>
                <p style="margin:0 0 12px;font-size:15px;line-height:1.6;">
                    Halo {{ $recipientName }},
                </p>
                <p style="margin:0 0 12px;font-size:15px;line-height:1.6;">
                    Pengajuan <strong>{{ $typeName }}</strong> untuk {{ $dateRange }} di {{ $tenantName }}
                    telah <strong>{{ strtolower($statusLabel) }}</strong>.
                </p>
                @if ($reviewNote)
                    <p style="margin:0 0 12px;font-size:15px;line-height:1.6;">
                        Catatan peninjau: {{ $reviewNote }}
                    </p>
                @endif
                <table role="presentation" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="background-color:#20261f;border-radius:6px;">
                            <a href="{{ $detailUrl }}"
                               style="display:inline-block;padding:12px 20px;color:#ffffff;font-size:15px;text-decoration:none;">
                               Lihat detail
                            </a>
                        </td>
                    </tr>
                </table>
                <p style="margin:24px 0 8px;font-size:13px;line-height:1.6;color:#666b61;">
                    Bila tombol di atas tidak berfungsi, salin tautan berikut ke peramban.
                </p>
                <p style="margin:0;font-size:13px;line-height:1.6;word-break:break-all;">
                    <a href="{{ $detailUrl }}" style="color:#3e463e;">{{ $detailUrl }}</a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:16px 4px;font-size:12px;color:#666b61;">
                Pesan ini dikirim karena ada perubahan status pengajuan izin pada akun Anda di {{ $tenantName }}.
            </td>
        </tr>
    </table>
</body>
</html>
