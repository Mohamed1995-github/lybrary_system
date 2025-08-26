'use client'

import { useState } from 'react'
import { useAuth } from '@/contexts/AuthContext'
import { useRouter } from 'next/navigation'
import Navbar from '@/components/Navbar'
import { ArrowLeft } from 'lucide-react'
import Link from 'next/link'

export default function AddCar() {
  const { user, token } = useAuth()
  const router = useRouter()
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [formData, setFormData] = useState({
    brand: '',
    model: '',
    year: new Date().getFullYear(),
    color: '',
    mileage: 0,
    fuel: 'ESSENCE',
    transmission: 'MANUELLE',
    price: 0,
    type: 'VENTE',
    description: ''
  })

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target
    setFormData(prev => ({
      ...prev,
      [name]: name === 'year' || name === 'mileage' || name === 'price' 
        ? parseFloat(value) || 0 
        : value
    }))
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    setError('')

    try {
      const response = await fetch('/api/cars', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(formData)
      })

      const data = await response.json()

      if (!response.ok) {
        throw new Error(data.error || 'Erreur lors de l\'ajout de la voiture')
      }

      router.push('/dashboard')
    } catch (error: any) {
      setError(error.message)
    } finally {
      setLoading(false)
    }
  }

  if (!user || user.role !== 'AGENCY') {
    router.push('/login')
    return null
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <Navbar />
      
      <div className="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-6">
          <Link
            href="/dashboard"
            className="flex items-center space-x-2 text-blue-600 hover:text-blue-800 mb-4"
          >
            <ArrowLeft className="h-5 w-5" />
            <span>Retour au tableau de bord</span>
          </Link>
          <h1 className="text-3xl font-bold text-gray-900">Ajouter une voiture</h1>
          <p className="text-gray-600">Ajoutez une nouvelle voiture à votre inventaire</p>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <form onSubmit={handleSubmit} className="space-y-6">
            {error && (
              <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {error}
              </div>
            )}

            {/* Informations de base */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label htmlFor="brand" className="block text-sm font-medium text-gray-700 mb-1">
                  Marque *
                </label>
                <input
                  type="text"
                  id="brand"
                  name="brand"
                  required
                  value={formData.brand}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="ex: Peugeot"
                />
              </div>

              <div>
                <label htmlFor="model" className="block text-sm font-medium text-gray-700 mb-1">
                  Modèle *
                </label>
                <input
                  type="text"
                  id="model"
                  name="model"
                  required
                  value={formData.model}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="ex: 308"
                />
              </div>

              <div>
                <label htmlFor="year" className="block text-sm font-medium text-gray-700 mb-1">
                  Année *
                </label>
                <input
                  type="number"
                  id="year"
                  name="year"
                  required
                  min="1990"
                  max={new Date().getFullYear() + 1}
                  value={formData.year}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              <div>
                <label htmlFor="color" className="block text-sm font-medium text-gray-700 mb-1">
                  Couleur *
                </label>
                <input
                  type="text"
                  id="color"
                  name="color"
                  required
                  value={formData.color}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="ex: Blanc"
                />
              </div>

              <div>
                <label htmlFor="mileage" className="block text-sm font-medium text-gray-700 mb-1">
                  Kilométrage *
                </label>
                <input
                  type="number"
                  id="mileage"
                  name="mileage"
                  required
                  min="0"
                  value={formData.mileage}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="ex: 50000"
                />
              </div>

              <div>
                <label htmlFor="fuel" className="block text-sm font-medium text-gray-700 mb-1">
                  Carburant *
                </label>
                <select
                  id="fuel"
                  name="fuel"
                  required
                  value={formData.fuel}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="ESSENCE">Essence</option>
                  <option value="DIESEL">Diesel</option>
                  <option value="HYBRIDE">Hybride</option>
                  <option value="ELECTRIQUE">Électrique</option>
                  <option value="GPL">GPL</option>
                </select>
              </div>

              <div>
                <label htmlFor="transmission" className="block text-sm font-medium text-gray-700 mb-1">
                  Transmission *
                </label>
                <select
                  id="transmission"
                  name="transmission"
                  required
                  value={formData.transmission}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="MANUELLE">Manuelle</option>
                  <option value="AUTOMATIQUE">Automatique</option>
                </select>
              </div>

              <div>
                <label htmlFor="type" className="block text-sm font-medium text-gray-700 mb-1">
                  Type *
                </label>
                <select
                  id="type"
                  name="type"
                  required
                  value={formData.type}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="VENTE">À vendre</option>
                  <option value="LOCATION">À louer</option>
                </select>
              </div>
            </div>

            {/* Prix */}
            <div>
              <label htmlFor="price" className="block text-sm font-medium text-gray-700 mb-1">
                Prix * {formData.type === 'LOCATION' && <span className="text-sm text-gray-500">(par jour)</span>}
              </label>
              <div className="relative">
                <input
                  type="number"
                  id="price"
                  name="price"
                  required
                  min="0"
                  step="0.01"
                  value={formData.price}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="ex: 15000"
                />
                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <span className="text-gray-500 text-sm">€</span>
                </div>
              </div>
            </div>

            {/* Description */}
            <div>
              <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-1">
                Description
              </label>
              <textarea
                id="description"
                name="description"
                rows={4}
                value={formData.description}
                onChange={handleInputChange}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Décrivez les caractéristiques et l'état de la voiture..."
              />
            </div>

            {/* Boutons */}
            <div className="flex space-x-4 pt-6">
              <button
                type="button"
                onClick={() => router.push('/dashboard')}
                className="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                Annuler
              </button>
              <button
                type="submit"
                disabled={loading}
                className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
              >
                {loading ? 'Ajout en cours...' : 'Ajouter la voiture'}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  )
}