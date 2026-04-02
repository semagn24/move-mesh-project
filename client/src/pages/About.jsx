const About = () => {
    return (
        <div className="max-w-4xl mx-auto py-12 text-center">
            <h1 className="text-5xl font-black mb-8 text-primary italic tracking-tighter">MOVIESTREAM</h1>
            <p className="text-xl text-gray-400 leading-relaxed mb-12">
                The ultimate destination for movie enthusiasts. We provide a seamless streaming experience
                with a vast library of high-quality content, ranging from the latest blockbusters to
                timeless classics.
            </p>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-8 text-left">
                <div className="glass p-8 rounded-2xl border border-white/5">
                    <i className="fa fa-bolt text-3xl text-primary mb-4"></i>
                    <h3 className="font-bold mb-2">High Speed</h3>
                    <p className="text-sm text-gray-500">Optimized streaming servers for buffer-free experience.</p>
                </div>
                <div className="glass p-8 rounded-2xl border border-white/5">
                    <i className="fa fa-mobile-alt text-3xl text-primary mb-4"></i>
                    <h3 className="font-bold mb-2">Responsive</h3>
                    <p className="text-sm text-gray-500">Watch on any device, from your mobile to your giant TV.</p>
                </div>
                <div className="glass p-8 rounded-2xl border border-white/5">
                    <i className="fa fa-shield-alt text-3xl text-primary mb-4"></i>
                    <h3 className="font-bold mb-2">Secure</h3>
                    <p className="text-sm text-gray-500">Your data and privacy are our top priority.</p>
                </div>
            </div>
        </div>
    );
};

export default About;
