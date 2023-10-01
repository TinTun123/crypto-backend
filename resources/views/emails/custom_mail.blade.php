<!DOCTYPE html>
<html>
<head>
    <!-- Any necessary meta tags, styles, or headers -->
</head>
<body>
    <table>
        <tr>
            <td>
                <img src="{{ asset('/logo.svg') }}" alt="{{ config('app.name') }}">
            </td>
        </tr>
        <tr>
            <td>
                <p>Your two-factor authentication code is:</p>
                <p>{{ $code }}</p> <!-- Include the 2FA code here -->
            </td>
        </tr>
        <tr>
            <td>
                <!-- Any additional content, formatting, or styling -->
            </td>
        </tr>
    </table>
</body>
</html>
