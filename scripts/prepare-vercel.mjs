import fs from 'node:fs'
import path from 'node:path'

const root = process.cwd()
const manifestPath = path.join(root, 'public/build/manifest.json')
const outputPath = path.join(root, 'public/index.html')

if (!fs.existsSync(manifestPath)) {
  console.error('Manifest i munguar: public/build/manifest.json — ekzekuto vite build së pari.')
  process.exit(1)
}

const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'))
const entry = manifest['resources/js/app.js']

if (!entry?.file) {
  console.error('Manifest nuk përmban resources/js/app.js')
  process.exit(1)
}

const cssFiles = [
  ...(entry.css ?? []),
  ...(manifest['resources/css/app.css']?.file && !(entry.css ?? []).includes(manifest['resources/css/app.css'].file)
    ? [manifest['resources/css/app.css'].file]
    : []),
]

const cssTags = cssFiles.map((file) => `    <link rel="stylesheet" href="/build/${file}" />`).join('\n')
const jsTag = `    <script type="module" src="/build/${entry.file}"></script>`

const html = `<!DOCTYPE html>
<html lang="sq">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="" />
    <title>Aron Trade</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40' fill='none'%3E%3Crect width='40' height='40' rx='8' fill='%230d9488'/%3E%3Ctext x='20' y='26' text-anchor='middle' fill='white' font-family='system-ui,sans-serif' font-weight='700' font-size='16'%3EAT%3C/text%3E%3C/svg%3E" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
${cssTags}
${jsTag}
    <style>
      footer{background-color:#000!important;color:#fff!important;}
      footer a{color:#9ca3af;}
      footer a:hover{color:#fff;}
      @media (max-width:767px){
        footer ul{display:flex!important;flex-direction:row!important;flex-wrap:wrap!important;gap:.5rem .75rem!important;font-size:12px!important;}
        footer ul li{white-space:nowrap!important;}
        footer .space-y-2>div{display:flex!important;flex-direction:row!important;flex-wrap:wrap!important;gap:.5rem .75rem!important;font-size:12px!important;}
        footer .space-y-2>div span{white-space:nowrap!important;}
      }
    </style>
  </head>
  <body class="font-sans antialiased">
    <div id="app"></div>
  </body>
</html>
`

fs.writeFileSync(outputPath, html)
console.log('Vercel SPA shell u gjenerua:', outputPath)
