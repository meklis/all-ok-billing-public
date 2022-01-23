<?php


namespace envPHP\EventSystem;


use envPHP\classes\Logger;

class EventRepository implements \SplSubject
{
    /**
     * @var array
     */
    private $observers = [];
    protected static $self = null;

    public static function getSelf()
    {
        if (self::$self == null) {
            throw new \Exception("event repository not initialized");
        }
        return self::$self;
    }

    public function __construct()
    {
        // Специальная группа событий для наблюдателей, которые хотят слушать
        // все события.
        $this->observers["*"] = [];
        self::$self = $this;
    }

    private function initEventGroup(string $event = "*"): void
    {
        if (!isset($this->observers[$event])) {
            $this->observers[$event] = [];
        }
    }

    private function getEventObservers(string $event = "*"): array
    {
        $observers = [];
        if (strpos($event, '*') !== false && strpos($event, ':') !== false) {
            $regex = '/^' . str_replace('*', ".*?", $event) . '$/';
            foreach ($this->observers as $eventName => $observer) {
                if (preg_match($regex, $event)) {
                    $observers = array_merge($observers, $this->observers[$eventName]);
                }
            }
        } else {
            $this->initEventGroup($event);
            $group = $this->observers[$event];
            $all = $this->observers["*"];
            $observers = array_merge($group, $all);
        }
        return $observers;
    }

    public function attach(\SplObserver $observer, string $event = "*"): void
    {
        $this->initEventGroup($event);

        $this->observers[$event][] = $observer;
    }

    public function attachAllDirectoryEvents()
    {
        $data = scandir(__DIR__ . '/../Events');
        foreach ($data as $d) {
            if (strpos($d, '.php') !== false) {
                $classname = "\\envPHP\\Events\\" . str_replace('.php', '', $d);
                $event = new $classname();
                $this->attach($event, $event->getEventType());
            }
        }
    }

    public function detach(\SplObserver $observer, string $event = "*"): void
    {
        foreach ($this->getEventObservers($event) as $key => $s) {
            if ($s === $observer) {
                unset($this->observers[$event][$key]);
            }
        }
    }

    public function notify(string $event = "*", $data = null): void
    {
        foreach ($this->getEventObservers($event) as $observer) {
            try {
                $observer->update($this, $event, $data);
            } catch (\Exception $e) {
                Logger::get()->error("except process with event {$event}: {$e->getMessage()}");
            }
        }
    }
}