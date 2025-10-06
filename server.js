const express = require('express');
const app = express();
const port = 3000;

app.get('/update', (req, res) => {
    const estado = req.query.estado;
    console.log(`Estado recibido: ${estado}`);
    res.send('Datos recibidos');
});

app.listen(port, () => {
    console.log(`Servidor escuchando en http://localhost:${port}`);
});// JavaScript source code
