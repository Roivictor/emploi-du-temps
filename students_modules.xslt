<!-- students_modules.xslt -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="/">
        <html>
        <head>
            <title>Étudiants et Modules</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
        </head>
        <body>
            <div class="container mt-5">
                <h2>Classe: <xsl:value-of select="classe/@filiere"/> - Niveau <xsl:value-of select="classe/@niveau"/></h2>
                <h3>Étudiants</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Numéro d'Inscription</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:for-each select="classe/etudiants/etudiant">
                            <tr>
                                <td><xsl:value-of select="@numInscription"/></td>
                                <td><xsl:value-of select="@nom"/></td>
                                <td><xsl:value-of select="@prenom"/></td>
                            </tr>
                        </xsl:for-each>
                    </tbody>
                </table>
                <h3>Modules</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID Module</th>
                            <th>Nom Module</th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:for-each select="classe/modules/module">
                            <tr>
                                <td><xsl:value-of select="@idModule"/></td>
                                <td><xsl:value-of select="@nomModule"/></td>
                            </tr>
                        </xsl:for-each>
                    </tbody>
                </table>
            </div>
        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>