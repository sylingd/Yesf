module.exports = {
  base: "/",
  title: "Yesf",
  description: "Yesf docs",
  markdown: {
    lineNumbers: true,
    toc: {
      includeLevel: [1, 2, 3]
    }
  },
  locales: {
    '/': {
      lang: 'zh-CN',
      title: 'Yesf',
      description: 'Yesf文档'
    },
    '/en/': {
      lang: 'en-US',
      title: 'Yesf',
      description: 'Yesf docs'
    }
  },
  themeConfig: {
    repo: "sylingd/Yesf",
    docsDir: 'docs',
    editLinks: true,
    locales: {
      '/': {
        lang: 'zh-CN',
        selectText: '选择语言',
        label: '简体中文',
        editLinkText: '在 GitHub 上编辑此页',
        nav: [{
          text: '首页',
          link: '/'
        }, {
          text: '指南',
          link: '/zh-CN/guide'
        }, {
          text: '镜像',
          items: [{
              text: '美国（由GitHub提供）',
              link: 'https://yesf.sylibs.com'
            },
            {
              text: '香港（由Gitee提供）',
              link: 'http://yesf-cn.sylibs.com'
            }
          ]
        }],
        sidebar: [
          '/zh-CN/guide',
          {
            title: '快速开始',
            path: '/zh-CN/start/',
            children: [
              '/zh-CN/start/hello_world',
              '/zh-CN/start/configuration',
            ]
          },
          '/zh-CN/container',
          {
            title: '请求处理',
            path: '/zh-CN/process_request/',
            children: [
              '/zh-CN/process_request/router',
              '/zh-CN/process_request/request',
              '/zh-CN/process_request/response'
            ]
          },
          {
            title: '插件',
            path: '/zh-CN/plugin/',
            children: [
              '/zh-CN/plugin/on_worker_start',
              '/zh-CN/plugin/on_before_route',
              '/zh-CN/plugin/on_before_dispatch',
              '/zh-CN/plugin/on_dispatch_failed',
              '/zh-CN/plugin/on_after_dispatch',
            ]
          },
          {
            title: '缓存',
            path: '/zh-CN/cache/',
            children: [
              '/zh-CN/cache/redis',
              '/zh-CN/cache/file',
              '/zh-CN/cache/yac',
              '/zh-CN/cache/custom',
            ]
          }
        ]
      },
      '/en/': {
        lang: 'en-US',
        selectText: 'Languages',
        label: 'English',
        editLinkText: 'Edit this page on GitHub',
        nav: [{
          text: 'Home',
          link: '/en/'
        }, {
          text: 'Guide',
          link: '/en/guide'
        }, {
          text: 'Mirrors',
          items: [{
              text: 'United States (Provided by GitHub)',
              link: 'https://yesf.sylibs.com'
            },
            {
              text: 'HongKong (Provided by Gitee)',
              link: 'http://yesf-cn.sylibs.com'
            }
          ]
        }],
        sidebar: []
      }
    }
  }
};