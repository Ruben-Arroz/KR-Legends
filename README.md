# KR Legends Official Website

![KR Legends](https://img.shields.io/badge/KR%20Legends-Website-black?style=for-the-badge)
![KR Company](https://img.shields.io/badge/KR%20Company-Official-black?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-Website-777BB4?style=for-the-badge&logo=php&logoColor=white)
![GitHub](https://img.shields.io/badge/Repository-GitHub-181717?style=for-the-badge&logo=github&logoColor=white)

> Official website repository for KR Legends — an open-world street drift game and companion website, originally created as a school PAP project and still growing today.

## Table of Contents

- [About KR Legends](#about-kr-legends)
- [KR Company](#kr-company)
- [Website](#website)
- [Project Vision](#project-vision)
- [Repository Scope](#repository-scope)
- [Project Structure](#project-structure)
- [Technology](#technology)
- [Architecture](#architecture)
- [Main Website Sections](#main-website-sections)
- [User System](#user-system)
- [Administration](#administration)
- [API](#api)
- [Assets](#assets)
- [Email Infrastructure](#email-infrastructure)
- [Security](#security)
- [Known Limitations](#known-limitations)
- [Local Development](#local-development)
- [Installation](#installation)
- [Development Workflow](#development-workflow)
- [Branching Strategy](#branching-strategy)
- [Development Principles](#development-principles)
- [Roadmap](#roadmap)
- [Future Plans](#future-plans)
- [KR Legends Ecosystem](#kr-legends-ecosystem)
- [Project Status](#project-status)
- [Contributions](#contributions)
- [Testing](#testing)
- [Deployment](#deployment)
- [License](#license)
- [Intellectual Property](#intellectual-property)
- [Links & Community](#links--community)

## About KR Legends

KR Legends started as a **Prova de Aptidão Profissional (PAP)** — the final project of the Técnico de Informática e Sistemas professional course — created by **Rúben Arroz** and **Kauã Severino Silva** at Agrupamento de Escolas Soares Basto (Oliveira de Azeméis, Portugal), during the 2024/2025 school year. Originally named *KR Drift Legends*, the project pairs an open-world street drift racing game built in Roblox Studio and inspired by JDM culture with a companion website built in PHP with a MySQL backend.

Both the game and the website are playable and mostly functional, but not fully finished — they were built by two students learning most of the underlying tools and languages (Lua, PHP, SQL, the Roblox API, and more) while developing the project itself. Some parts are still rough around the edges; see [Known Limitations](#known-limitations).

This repository contains the official website of KR Legends, including its frontend, backend, administrative components, user systems, APIs, assets and supporting infrastructure. The game itself lives in Roblox Studio and is not part of this repository.

The website serves as the main online platform for the project, providing information, updates, community features, an online store and a foundation for future services connected to KR Legends.

## KR Company

KR Company isn't a registered company today — it's an unofficial brand idea the creators have for the future: a shared name under which KR Legends, and possibly other projects down the line, could one day sit. The project's original PAP report describes this ambition directly, noting that the KR Legends prototype could eventually grow into an actual venture focused on game development and digital services.

For now, KR Legends is the only project that exists under that idea:

- KR Legends (live)
- Future KR-branded projects — conceptual only; an earlier working idea for one was called KR Stellar Odyssey, but nothing has been built yet

This is why parts of this repository and website refer to "KR Company" as if it already existed — it's aspirational branding the team hopes to grow into, not a legal entity today.

## Website

The KR Legends website is designed to be the main online presence of the project. It provides a foundation for the website to grow alongside the development of the game.

### Main Objectives

- Present KR Legends to visitors
- Provide official project information
- Maintain the visual identity of KR Legends
- Support community interaction
- Provide a foundation for user accounts
- Centralize game related content
- Support administrative management
- Provide infrastructure for future KR Legends features and services

## Project Vision

The website is being developed as a proper platform rather than a simple static page. Its structure allows new sections and functionality to be added as KR Legends continues to develop. The diagram below sketches the long-term vision the creators have in mind — it isn't a confirmed org chart, and today only KR Legends itself exists.

```text
                         KR COMPANY
                              │
              ┌───────────────┴───────────────┐
              │                               │
         KR LEGENDS                    OTHER KR PROJECTS
              │
              │
       ┌──────┴───────┐
       │              │
    WEBSITE        GAME / IP
       │
 ┌─────┼──────┬─────────────┐
 │     │      │             │
Public Users Community Administration
       │
       └───────────────┬───────────────┘
                       │
                 KR Ecosystem
```

## Repository Scope

This repository is dedicated specifically to the KR Legends website. It contains several parts of the website infrastructure, including:

- Public website pages
- PHP backend components
- CSS stylesheets
- JavaScript functionality
- API related components
- User authentication and registration systems
- Administrative tools
- User related functionality
- Shared includes
- Images and visual assets
- JSON data
- Email related components
- Supporting PHP utilities
- Server configuration files

This repository is intended for the web platform of KR Legends and does not represent the source code of the game itself.

## Project Structure

A simplified view of the repository's top level is shown below. It isn't an exhaustive file listing — the site has grown to include more pages and subfolders than shown here, and the structure keeps changing as development continues.

```text
KR-Legends/
│
├── Anexos/
├── Imagens/
├── JSON/
├── Login-Cadastro/
├── PHP/
├── PHPMailer-6.10.0/
├── Utilizador/
├── admin/
├── api/
├── css/
├── favicon_io/
├── includes/
├── js/
│
├── .gitattributes
├── .htaccess
│
├── 404.php
├── Atualizacoes.php
├── Comunidade.php
├── Creditos.php
├── Equipa.php
├── Galeria.php
├── Index.php
├── PAP.php
│
└── README.md
```

## Technology

The website currently uses a PHP based architecture together with standard frontend technologies and supporting services.

| Technology | Purpose |
| --- | --- |
| PHP | Server side application logic and dynamic pages |
| MySQL | Database for user accounts, content and site data |
| HTML | Website structure and content |
| CSS | Visual design and responsive presentation |
| JavaScript | Client side functionality and interaction |
| JSON | Structured data and configuration resources |
| Roblox API | Integration with the KR Legends Roblox game |
| Apache | Web server environment |
| .htaccess | Server configuration and routing related behavior |
| PHPMailer | Email related functionality |

The technology stack may evolve as the project develops.

## Architecture

The website follows a modular structure where different responsibilities are kept in separate directories and components. A simplified view of the architecture is:

```text
                    ┌─────────────────────┐
                    │       VISITOR       │
                    └──────────┬──────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │    PUBLIC WEBSITE   │
                    │      PHP / HTML     │
                    └──────────┬──────────┘
                               │
                ┌──────────────┼──────────────┐
                │              │              │
                ▼              ▼              ▼
              CSS        JavaScript          APIs
                │              │              │
                └──────────────┼──────────────┘
                               │
                               ▼
                    ┌─────────────────────┐
                    │    PHP BACKEND      │
                    └──────────┬──────────┘
                               │
             ┌─────────────────┼─────────────────┐
             │                 │                 │
             ▼                 ▼                 ▼
          Accounts          Users          Administration
```

This structure makes it easier to maintain the website and add new functionality without mixing unrelated systems.

## Main Website Sections

The site includes many pages beyond what's listed here — among others, an online store, rankings, game modes, account settings and a role-based admin panel. This isn't meant to be a complete list, just a few of the main public pages as a starting point:

### Index.php
Main website entry point.

### Atualizacoes.php
Section dedicated to project updates and development information.

### Comunidade.php
Community related section.

### Creditos.php
Credits and acknowledgements.

### Equipa.php
Information related to the development team.

### Galeria.php
Gallery and visual media section.

### PAP.php
Page dedicated to the project's origin as a Prova de Aptidão Profissional.

Individual sections may change as development progresses.

## User System

The repository contains dedicated components for user functionality. Relevant directories include:

- `Login-Cadastro/`
- `Utilizador/`

These areas provide the foundation for functionality related to user accounts, authentication, registration and other account related features (profile editing, account settings, etc.). The user system can be expanded as new community features are introduced.

## Administration

The repository contains a dedicated `admin/` area, used for administrative functionality and internal website management. Access is role-based (regular user / admin / super-admin), with separate admin pages covering areas such as team info, gallery content, user management and support.

Administrative functionality should remain separated from public functionality, and access should be restricted appropriately when deployed.

## API

The repository contains an `api/` directory, providing a dedicated location for API related functionality, including integration with Roblox's public API. One past feature used this to report live player counts from the KR Legends Roblox experience on the website; it isn't active at the moment since development has slowed down, but the underlying pieces are still part of the codebase.

The API layer can act as a connection point between the website and future services within the KR Legends ecosystem.

```text
Website
   │
   ├── User services
   ├── Community systems
   ├── Content services
   ├── Authentication
   ├── Administration
   └── Future KR systems
```

## Assets

Visual resources are stored in dedicated directories:

- `Imagens/`
- `favicon_io/`
- `css/`
- `js/`

These directories separate images, branding resources, styling and client side functionality. As the website grows, these directories should remain organized and avoid unnecessary duplication.

## Email Infrastructure

The repository contains `PHPMailer-6.10.0/`, which provides the email infrastructure used by the website where required.

Email functionality may support services such as:

- Account related communication
- Registration processes
- Notifications
- Contact systems
- Administrative communication
- Community services

Credentials and sensitive configuration values must never be committed to the repository.

## Security

Security is an important part of the website architecture. The following practices should be followed throughout development:

- Never commit passwords or secret keys
- Never expose database credentials
- Never commit private API keys
- Validate and sanitize user input
- Protect authentication systems
- Restrict administrative functionality
- Keep server configuration secure
- Keep third party dependencies updated
- Avoid storing sensitive information directly in public files
- Review permissions before production deployment

Sensitive configuration should preferably remain outside the repository or be provided through server or environment configuration.

## Known Limitations

KR Legends started as a learning project — both the game and the website were built by two students picking up most of the required tools and languages (Lua, PHP, SQL, the Roblox API, and more) along the way. As a result, some parts are still incomplete or rough around the edges: for example, the website's multi-language support currently covers only part of the content rather than everything, and not every security best-practice has been fully implemented yet. These are known, ongoing areas for improvement rather than hidden issues.

## Local Development

To run the website locally, a PHP compatible development environment is required. A typical environment may look like this:

```text
Web Server
   │
   ├── Apache
   │
   └── PHP
        │
        └── KR Legends Website
```

Common development environments include:

- XAMPP
- WAMP
- Laragon
- A manually configured Apache and PHP environment

## Installation

Clone the repository:

```bash
git clone https://github.com/Ruben-Arroz/KR-Legends.git
```

Enter the project directory:

```bash
cd KR-Legends
```

Configure the project inside your PHP web server environment. For example, with Apache:

```text
htdocs/
└── KR-Legends/
```

Start the required services and open the website through your local development server.

## Development Workflow

The recommended development process is:

```text
Idea
  ↓
Development
  ↓
Local Testing
  ↓
Bug Fixing
  ↓
Code Review
  ↓
Git Commit
  ↓
GitHub
  ↓
Deployment
```

Before committing changes:

```bash
git status
```

Review the modified files and then commit:

```bash
git add .
git commit -m "Describe your change"
git push
```

Commit messages should clearly describe the change. Examples:

- Add community page
- Fix authentication redirect
- Update website gallery
- Improve responsive navigation
- Add new API endpoint
- Fix mobile layout

## Branching Strategy

As the project grows, development can be organized using separate branches:

```text
main
│
├── development
│
├── feature/feature-name
│
├── fix/bug-name
│
└── redesign/section-name
```

### main
Stable version of the website.

### development
Active development branch.

### feature/*
New functionality.

### fix/*
Bug fixes and maintenance.

### redesign/*
Major design or interface changes.

## Development Principles

### Scalability
The website should be able to expand alongside KR Legends.

### Maintainability
Code should remain understandable and organized as the project grows.

### Modularity
Components should be separated according to their responsibilities.

### Security
Sensitive systems and data should be handled correctly.

### Performance
Pages and assets should be optimized for a responsive experience.

### Consistency
The visual identity of the website should remain consistent with KR Legends.

### Future Compatibility
New systems should take future integrations into account.

## Roadmap

The ideas below come from KR Legends' original PAP final report (mid-2025) and reflect the ambitions the creators had at the time. It's a rough, non-exhaustive sketch of where the project could go — not a committed plan:

- Growing KR Legends into one of the most played Roblox-made drift/racing titles in Portugal, and eventually competing in global rankings
- Exploring monetization through Robux-to-cash conversion (Roblox DevEx), season passes, premium content and creator/brand partnerships
- Continuing to build the technical and organizational skills that could, further down the line, support turning the KR Legends prototype into an actual small studio or business

See [Future Plans](#future-plans) for what the creators are currently considering next.

## Future Plans

Development has slowed since the PAP was submitted, but the project hasn't been abandoned. The main idea being considered is rebuilding the game in Unreal Engine, so it can eventually be published beyond Roblox on other gaming platforms. At the same time, there's a wish to keep the existing Roblox Studio version alive and updated where possible, rather than dropping it entirely — it's where KR Legends started, and the team isn't ready to let it go.

Nothing here is scheduled or guaranteed; these are directions the creators are exploring as time and other projects allow.

## KR Legends Ecosystem

The website is one of the digital components supporting KR Legends. As with the [Project Vision](#project-vision) diagram earlier, this reflects the long-term vision rather than the current state — today, KR Legends is the only project that exists under the KR Company idea.

```text
                           KR COMPANY
                               │
              ┌────────────────┼────────────────┐
              │                │                │
              ▼                ▼                ▼
         KR LEGENDS      KR STELLAR       FUTURE PROJECTS
                           ODYSSEY
              │
              ▼
       KR LEGENDS WEBSITE
              │
      ┌───────┼────────┐
      │       │        │
      ▼       ▼        ▼
   Players Community Website
      │       │        │
      └───────┼────────┘
              ▼
       KR DIGITAL ECOSYSTEM
```

## Project Status

**Development Status:** Paused, not abandoned

KR Legends was completed as a PAP project in June 2025, reaching a playable Beta on both the game and the website. Active development has slowed down since then while the creators consider next steps (see [Future Plans](#future-plans)), but updates may resume at any time.

## Contributions

The KR Legends website is a personal/school project created by its founders. Development should follow the project's existing coding standards, architecture and security practices.

Before introducing major changes, make sure they:

- Have a clear purpose
- Do not unnecessarily break existing functionality
- Follow the existing project structure
- Are tested locally
- Do not expose sensitive information
- Keep the project maintainable

## Testing

Before deployment, verify at minimum:

- ✓ Homepage
- ✓ Navigation
- ✓ Authentication
- ✓ Registration
- ✓ User functionality
- ✓ Administrative functionality
- ✓ API endpoints
- ✓ Forms
- ✓ Email functionality
- ✓ Images and assets
- ✓ Mobile layout
- ✓ Desktop layout
- ✓ Error pages
- ✓ Server configuration

Additional automated testing can be introduced as the project architecture matures.

## Deployment

Production deployment should only happen after the required functionality has been tested. A typical deployment process is:

```text
Local Development
       ↓
Testing
       ↓
Review
       ↓
GitHub
       ↓
Production Deployment
       ↓
Monitoring
```

Production credentials and sensitive environment specific configuration must not be committed to the repository.

## License

The licensing status of this repository is currently not publicly specified.

Unless a license is explicitly added to the repository, users should not assume that the source code is freely reusable.

## Intellectual Property

KR Legends is a personal project created by Rúben Arroz and Kauã Severino Silva, originally developed as a Prova de Aptidão Profissional (PAP) at Agrupamento de Escolas Soares Basto. "KR Company" is an aspirational brand name for potential future projects and is not currently a registered company. Project names, branding, original assets, designs and content associated with KR Legends belong to its creators.

## Links & Community

- **Website:** [alpha.soaresbasto.pt/~a29621/KRLegends](https://alpha.soaresbasto.pt/~a29621/KRLegends/)
- **Repository:** [github.com/Ruben-Arroz/KR-Legends](https://github.com/Ruben-Arroz/KR-Legends)
- **Game (Roblox, Beta):** [KR Legends on Roblox](https://www.roblox.com/pt/games/113586179382037/KR-Legends)
- **Roblox Community:** [KR Legends Group](https://www.roblox.com/pt/communities/36061138/KR-Legends-Group)
- **Social media:** Instagram ([@kr_legends.oficial](https://www.instagram.com/kr_legends.oficial/)) · YouTube ([@KRLegends-media](https://www.youtube.com/@KRLegends-media)) · TikTok ([@kr.legends](https://www.tiktok.com/@kr.legends))
- **Final presentation (Canva):** [View presentation](https://www.canva.com/design/DAGr2_hzTn0/jG4W--aqJqVwfWSJSFS1iQ/view)
- **Final report (PDF, PT):** [Relatório final](https://alpha.soaresbasto.pt/~a29621/KRLegends/Anexos/Relatorios/Relatorio_final_Ruben.pdf)

This list may not reflect every active channel — other platforms (Threads, Guilded, Discord) have also been used for the community at different points.

---

<p align="center">
  <strong>KR Legends</strong><br>
  Built by KR Company.
</p>
