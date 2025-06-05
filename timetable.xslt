<!-- timetable.xslt -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="/">
        <html>
        <head>
            <title>Emploi du Temps</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
        </head>
        <body>
            <div class="container mt-5">
                <h2>Emploi du Temps - Classe <xsl:value-of select="emploi/@classe"/></h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Jour</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Professeur</th>
                            <th>Module</th>
                            <th>Salle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:for-each select="emploi/seance">
                            <tr>
                                <td><xsl:value-of select="@jour"/></td>
                                <td><xsl:value-of select="@debut"/></td>
                                <td><xsl:value-of select="@fin"/></td>
                                <td><xsl:value-of select="@prof"/></td>
                                <td><xsl:value-of select="@module"/></td>
                                <td><xsl:value-of select="@salle"/></td>
                            </tr>
                        </xsl:for-each>
                    </tbody>
                </table>
            </div>
        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>