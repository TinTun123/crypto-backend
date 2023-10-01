@props(['url'])

<tr>
    <td class="header">
    <div style="display: inline-flex; align-items: center; gap: 1rem; ">
        <a href="{{ $url }}" style="display: block; height: 48px; width : 40%; margin : auto">
            <img src="http:localhost:8000/something.png" style="display: block; height : 100%; width : 100%" class="logo" alt="Secwallet logo">
        </a>

        <span style="color: #F7BF4F; 
        font-family: Roboto;
        font-size: 2rem;
        font-style: italic;
        font-weight: 700;
        line-height: normal;">Secwallet</span>
    </div>

    </td>
</tr>
