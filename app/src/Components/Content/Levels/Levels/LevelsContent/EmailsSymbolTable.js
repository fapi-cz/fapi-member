import React from 'react';

function EmailsSymbolTable() {
  return (
    <table className="wp-list-table widefat fixed striped levels-content-after" style={{marginTop: '30px'}}>
        <thead>
        <tr>
            <th rowSpan="2" style={{width: '200px'}}>Kód</th>
            <th rowSpan="2">Popis</th>
            <th rowSpan="2">Příklad</th>
            <th colSpan="3" style={{width: '300px'}}>Dostupné při</th>
        </tr>
        <tr>
            <th>registraci nového člena</th>
            <th>prodloužení/přidání sekce
            </th>
            <th>prodloužení/přidání sekce
            </th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><code>%%SEKCE%%</code></td>
            <td>Název sekce</td>
            <td>Italská kuchyně</td>
            <td>✗</td>
            <td>✓</td>
            <td>✗</td>
        </tr>
        <tr>
            <td><code>%%UROVEN%%</code></td>
            <td>Název úrovně</td>
            <td>Začátečník</td>
            <td>✗</td>
            <td>✗</td>
            <td>✓</td>
        </tr>
        <tr>
            <td><code>%%DNI%%</code></td>
            <td>Počet zakoupených dní nebo 'neomezeně'</td>
            <td>31</td>
            <td>✗</td>
            <td>✓</td>
            <td>✓</td>
        </tr>
        <tr>
            <td><code>%%CLENSTVI_DO%%</code></td>
            <td>Datum konce členství nebo 'neomezené'</td>
            <td>12. 1. 2022</td>
            <td>✗</td>
            <td>✓</td>
            <td>✓</td>
        </tr>
        <tr>
            <td><code>%%PRIHLASENI_ODKAZ%%</code></td>
            <td>Odkaz na přihlášení (z nastavení) pouze URL</td>
            <td>https://www.example.com/login</td>
            <td>✓</td>
            <td>✓</td>
            <td>✓</td>
        </tr>
        <tr>
            <td><code>%%PRIHLASOVACI_JMENO%%</code></td>
            <td>Přihlašovací jméno uživatele</td>
            <td>jan@example.com</td>
            <td>✓</td>
            <td>✗</td>
            <td>✗</td>
        </tr>
        <tr>
            <td><code>%%HESLO%%</code></td>
            <td>Přihlašovací heslo uživatele</td>
            <td>)7PQll6Pw)HN7%w8ddES!ues</td>
            <td>✓</td>
            <td>✗</td>
            <td>✗</td>
        </tr>
        </tbody>
    </table>
  );
}

export default EmailsSymbolTable;
