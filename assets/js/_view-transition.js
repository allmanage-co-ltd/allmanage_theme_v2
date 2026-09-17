window.addEventListener('pagereveal', (e) => {
    if (!e.viewTransition) return;
    const isBack = navigation.activation?.navigationType === 'traverse';
    if (isBack) {
        e.viewTransition.types.add('back');
    }
});
