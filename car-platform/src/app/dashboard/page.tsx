'use client'

import { useState, useEffect } from 'react'
import { useAuth } from '@/contexts/AuthContext'
import { useRouter } from 'next/navigation'
import Navbar from '@/components/Navbar'
import { Plus, Edit, Trash2, Eye, EyeOff, MessageCircle } from 'lucide-react'
import Link from 'next/link'

interface Car {
  id: string
  brand: string
  model: string
  year: number
  color: string
  mileage: number
  fuel: string
  transmission: string
  price: number
  type: string
  description?: string
  available: boolean
  createdAt: string
}

interface Message {
  id: string
  content: string
  status: string
  createdAt: string
  user: {
    id: string
    name: string
    email: string
    phone?: string
  }
  car?: {
    id: string
    brand: string
    model: string
    year: number
  }
}

export default function Dashboard() {
  const { user, loading: authLoading } = useAuth()
  const router = useRouter()
  const [cars, setCars] = useState<Car[]>([])
  const [messages, setMessages] = useState<Message[]>([])
  const [activeTab, setActiveTab] = useState<'cars' | 'messages'>('cars')
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    if (!authLoading) {
      if (!user || user.role !== 'AGENCY') {
        router.push('/login')
        return
      }
      fetchCars()
      fetchMessages()
    }
  }, [user, authLoading, router])

  const fetchCars = async () => {
    try {
      const response = await fetch('/api/cars/my-cars', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        setCars(data)
      }
    } catch (error) {
      console.error('Erreur lors du chargement des voitures:', error)
    } finally {
      setLoading(false)
    }
  }

  const fetchMessages = async () => {
    try {
      const response = await fetch('/api/messages', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        setMessages(data)
      }
    } catch (error) {
      console.error('Erreur lors du chargement des messages:', error)
    }
  }

  const toggleAvailability = async (carId: string, available: boolean) => {
    try {
      const response = await fetch(`/api/cars/${carId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ available: !available })
      })

      if (response.ok) {
        setCars(cars.map(car => 
          car.id === carId ? { ...car, available: !available } : car
        ))
      }
    } catch (error) {
      console.error('Erreur lors de la mise à jour:', error)
    }
  }

  const deleteCar = async (carId: string) => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette voiture ?')) {
      return
    }

    try {
      const response = await fetch(`/api/cars/${carId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })

      if (response.ok) {
        setCars(cars.filter(car => car.id !== carId))
      }
    } catch (error) {
      console.error('Erreur lors de la suppression:', error)
    }
  }

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('fr-FR', {
      style: 'currency',
      currency: 'EUR'
    }).format(price)
  }

  const getFuelLabel = (fuel: string) => {
    const labels: { [key: string]: string } = {
      ESSENCE: 'Essence',
      DIESEL: 'Diesel',
      HYBRIDE: 'Hybride',
      ELECTRIQUE: 'Électrique',
      GPL: 'GPL'
    }
    return labels[fuel] || fuel
  }

  const getTypeLabel = (type: string) => {
    return type === 'VENTE' ? 'À vendre' : 'À louer'
  }

  if (authLoading || loading) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Navbar />
        <div className="flex justify-center items-center h-64">
          <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
      </div>
    )
  }

  if (!user || user.role !== 'AGENCY') {
    return null
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Navbar />
      
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex justify-between items-center mb-8">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Tableau de bord</h1>
            <p className="text-gray-600">{user.agency?.name}</p>
          </div>
          <Link
            href="/add-car"
            className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center space-x-2"
          >
            <Plus className="h-5 w-5" />
            <span>Ajouter une voiture</span>
          </Link>
        </div>

        {/* Statistiques */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-medium text-gray-900 mb-2">Total des voitures</h3>
            <p className="text-3xl font-bold text-blue-600">{cars.length}</p>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-medium text-gray-900 mb-2">Disponibles</h3>
            <p className="text-3xl font-bold text-green-600">
              {cars.filter(car => car.available).length}
            </p>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-medium text-gray-900 mb-2">Non disponibles</h3>
            <p className="text-3xl font-bold text-red-600">
              {cars.filter(car => !car.available).length}
            </p>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-medium text-gray-900 mb-2">Messages reçus</h3>
            <p className="text-3xl font-bold text-purple-600">{messages.length}</p>
          </div>
        </div>

        {/* Onglets */}
        <div className="bg-white rounded-lg shadow mb-6">
          <div className="border-b border-gray-200">
            <nav className="-mb-px flex">
              <button
                onClick={() => setActiveTab('cars')}
                className={`py-4 px-6 border-b-2 font-medium text-sm ${
                  activeTab === 'cars'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                Mes voitures ({cars.length})
              </button>
              <button
                onClick={() => setActiveTab('messages')}
                className={`py-4 px-6 border-b-2 font-medium text-sm flex items-center space-x-2 ${
                  activeTab === 'messages'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                <MessageCircle className="h-4 w-4" />
                <span>Messages ({messages.length})</span>
              </button>
            </nav>
          </div>
        </div>

        {/* Contenu des onglets */}
        {activeTab === 'cars' ? (
          <div className="bg-white rounded-lg shadow overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200">
              <h2 className="text-xl font-semibold text-gray-900">Mes voitures</h2>
            </div>
          
          {cars.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Véhicule
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Prix
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Type
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Statut
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {cars.map((car) => (
                    <tr key={car.id}>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div>
                          <div className="text-sm font-medium text-gray-900">
                            {car.brand} {car.model}
                          </div>
                          <div className="text-sm text-gray-500">
                            {car.year} • {car.color} • {getFuelLabel(car.fuel)}
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm font-medium text-gray-900">
                          {formatPrice(car.price)}
                          {car.type === 'LOCATION' && <span className="text-xs text-gray-500">/jour</span>}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${
                          car.type === 'VENTE'
                            ? 'bg-green-100 text-green-800'
                            : 'bg-blue-100 text-blue-800'
                        }`}>
                          {getTypeLabel(car.type)}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${
                          car.available
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800'
                        }`}>
                          {car.available ? 'Disponible' : 'Non disponible'}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <button
                          onClick={() => toggleAvailability(car.id, car.available)}
                          className="text-blue-600 hover:text-blue-900"
                          title={car.available ? 'Masquer' : 'Afficher'}
                        >
                          {car.available ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                        </button>
                        <button
                          onClick={() => router.push(`/edit-car/${car.id}`)}
                          className="text-indigo-600 hover:text-indigo-900"
                          title="Modifier"
                        >
                          <Edit className="h-4 w-4" />
                        </button>
                        <button
                          onClick={() => deleteCar(car.id)}
                          className="text-red-600 hover:text-red-900"
                          title="Supprimer"
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <div className="text-center py-12">
              <p className="text-xl text-gray-600 mb-4">Aucune voiture ajoutée</p>
              <p className="text-gray-500 mb-6">Commencez par ajouter votre première voiture</p>
              <Link
                href="/add-car"
                className="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 inline-flex items-center space-x-2"
              >
                <Plus className="h-5 w-5" />
                <span>Ajouter une voiture</span>
              </Link>
            </div>
          )}
          </div>
        ) : (
          <div className="bg-white rounded-lg shadow overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-200">
              <h2 className="text-xl font-semibold text-gray-900">Messages reçus</h2>
            </div>
            
            {messages.length > 0 ? (
              <div className="divide-y divide-gray-200">
                {messages.map((message) => (
                  <div key={message.id} className="p-6">
                    <div className="flex justify-between items-start mb-3">
                      <div>
                        <h3 className="text-lg font-medium text-gray-900">{message.user.name}</h3>
                        <p className="text-sm text-gray-500">{message.user.email}</p>
                        {message.user.phone && (
                          <p className="text-sm text-gray-500">{message.user.phone}</p>
                        )}
                      </div>
                      <span className="text-sm text-gray-400">
                        {new Date(message.createdAt).toLocaleDateString('fr-FR', {
                          year: 'numeric',
                          month: 'long',
                          day: 'numeric',
                          hour: '2-digit',
                          minute: '2-digit'
                        })}
                      </span>
                    </div>
                    
                    {message.car && (
                      <div className="mb-3 p-3 bg-gray-50 rounded-lg">
                        <p className="text-sm font-medium text-gray-700">
                          Concernant: {message.car.brand} {message.car.model} ({message.car.year})
                        </p>
                      </div>
                    )}
                    
                    <p className="text-gray-700 mb-4">{message.content}</p>
                    
                    <div className="flex space-x-3">
                      <a
                        href={`tel:${message.user.phone}`}
                        className="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                      >
                        Appeler
                      </a>
                      <a
                        href={`mailto:${message.user.email}?subject=Re: ${message.car ? `${message.car.brand} ${message.car.model}` : 'Votre message'}`}
                        className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                      >
                        Répondre par email
                      </a>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-12">
                <MessageCircle className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <p className="text-xl text-gray-600 mb-4">Aucun message reçu</p>
                <p className="text-gray-500">Les messages des clients apparaîtront ici</p>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  )
}